<?php

namespace App\Controller\Backoffice;

use App\Entity\Game;
use App\Entity\User;
use App\Form\GameType;
use App\Repository\GameRepository;
use App\Security\Voter\GameVoter;
use App\Service\CascadeTrashed;
use App\Service\MySlugger;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * @Route("/back/jeux")
 */
class GameController extends AbstractController
{
    private $paramBag;

    public function __construct(ParameterBagInterface $paramBag)
    {
        $this->paramBag = $paramBag;
    }
    /**
     * @Route("/", name="app_backoffice_game_index", methods={"GET"})
     */
    public function index(GameRepository $gameRepository): Response
    {
        // It's getting the user id of the user connected.
        $userConnected = $this->getUser()->getId();
        //dump($userConnected);
        $gameUser = $gameRepository->findBy(['user' => $userConnected]);
        $gameUserCreated = $gameUser[0];
        $thisPlayersGames= $gameUserCreated->getUser()->getId();
        //dump($thisUser);

        // It's checking if the user connected is the owner of the game.
        if ($userConnected == $thisPlayersGames)
        {
            return $this->render('backoffice/game/index.html.twig', [
                'actives_games' => $gameRepository->findBy(['status' => 1, 'isTrashed' => 0, 'user' => $userConnected]),
                'inactives_games' => $gameRepository->findBy(['status' => 0, 'isTrashed' => 0, 'user' => $userConnected]),
            ]);
        }
    }

    /**
     * @Route("/nouveau", name="app_backoffice_game_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager, MySlugger $mySlugger): Response
    {
        $game = new Game();
        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $mySlugger->slugify($game->getTitle());
            $game->setSlug($slug);
            $game->setUser($this->getUser());
            
            $file = $form['image']->getData();
            $filename = $slug.'.'.$file->guessExtension();
            $file->move($this->paramBag->get('app.game_images_directory'), $filename);
            $game->setImage($filename);
            $entityManager->persist($game);
            $entityManager->flush();

            $this->addFlash(
                'notice-success',
                'Le jeu '.$game->getTitle().' a été créé !'
            );

            return $this->redirectToRoute('app_backoffice_game_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('backoffice/game/new.html.twig', [
            'game' => $game,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{slug}", name="app_backoffice_game_show", methods={"GET"})
     */
    public function show(Game $game): Response
    {
        $instances = $game->getUnTrashedInstances()->getValues();
        $date = new DateTime();
        $date = $date->getTimestamp();
        foreach($instances AS $key=> $instance)
        {
            if($date > $instance->getStartAt()->getTimestamp() && $date < $instance->getEndAt()->getTimestamp())
            {
                unset($instances[$key]);
                array_unshift($instances, $instance);
            }
        }
        return $this->render('backoffice/game/show.html.twig', [
            'game' => $game,
            'instances' => $instances
        ]);
    }

    /**
     * @Route("/{slug}/modifier", name="app_backoffice_game_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Game $game, EntityManagerInterface $entityManager): Response
    {
        // Organizer or Admin can modify this game
        $this->denyAccessUnlessGranted('EDIT_GAME', $game);

        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash(
                'notice-success',
                'Le jeu '.$game->getTitle().' a été modifié !'
            );

            return $this->redirectToRoute('app_backoffice_game_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('backoffice/game/edit.html.twig', [
            'game' => $game,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_backoffice_game_delete", methods={"POST"}, requirements={"id"="\d+"})
     */
    public function delete(Request $request, Game $game, CascadeTrashed $cascadeTrashed): Response
    {

        // Organizer or Admin can modify this game
        $this->denyAccessUnlessGranted('DELETE_GAME', $game); 

        if ($this->isCsrfTokenValid('delete'.$game->getId(), $request->request->get('_token')))
        {
            $cascadeTrashed->trashGame($game);
            $this->addFlash(
                'notice-success',
                'Le jeu '.$game->getTitle().' a été supprimé ! Tous ses checkpoints, questions et instances ont été mis à la poubelle !'
            );
        } 
        else
        {
            $this->addFlash(
                'notice-danger',
                'Impossible de supprimer le jeu '.$game->getTitle().', token invalide !'
            );

        }

        return $this->redirectToRoute('app_backoffice_game_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/status/{id}",name="app_backoffice_update_status", methods={"POST"}, requirements={"id"="\d+"})
     *
     * @param Game $game
     * @return void
     */
    public function update_status(Request $request, Game $game, EntityManagerInterface $entityManager)
    {
        // Organizer or Admin can modify this game
        $this->denyAccessUnlessGranted('EDIT_GAME', $game);

        if ($this->isCsrfTokenValid('trash'.$game->getId(), $request->request->get('_token')))

        {
            if($game->getStatus())
            {
                $game->setStatus(false);
                $this->addFlash(
                    'notice-success',
                    'Le jeu '.$game->getTitle().' a été désactivé !'
                );
            }
            else
            {
                $game->setStatus(true);
                $this->addFlash(
                    'notice-success',
                    'Le jeu '.$game->getTitle().' a été activé !'
                );
            }

            $entityManager->flush();
        } 
        else
        {
            $this->addFlash(
                'notice-danger',
                'Impossible de désactiver le jeu '.$game->getTitle().', token invalide !'
            );
        }
        return $this->redirectToRoute('app_backoffice_game_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * PDF generator
     * 
     * @Route("/{slug}/pdf", name="app_backoffice_game_pdf", methods={"GET"})
     * 
     * @return void
     */
    public function pdf(Game $game): Response
    {
        /***** On prépare les données à insérer dans le PDF *****/

        // Titre des pages
        $title = $game->getTitle();
        // On converti de utf-8 vers ISO-8859-1 pour gérer les accents
        $title = utf8_decode($title);

        /***** On génére le document PDF *****/

        // On récupère la liste des checkpoints dans l'ordre de l'utilisateur
        $checkpointsList = $game->getCheckpoints();

        // Création d'un nouvel objet (document PDF)
        $pdf = new \FPDF();

        // On boucle sur la liste des checkpoints
        foreach ($checkpointsList as $checkpoint) {
            // Ajout d'une nouvelle page, avec ses header et footer
            $pdf->AddPage();

            // Réglage de la police
            $pdf->SetFont('Arial', 'B', 60);

            // Titre en haut de page, dans une cellule avec passage à la ligne et création d'une nouvelle cellule si trop long (MultiCell)
            $pdf->MultiCell(0, 20, $title, 0, 'C');
            // Saut de ligne
            $pdf->Ln();
            // Déplacement du curseur sur axe X pour centrage du QR code
            $pdf->SetX(45);
            // Insertion du QR code
            $pdf->Image($this->paramBag->get('app.game_qrcode_directory').$checkpoint->getId().'qrcode.png', null, null, 120);
        }

        /***** On traite le document PDF généré *****/

        // On enregistre le PDF dans le dossier "pdf"
        // $pdf->Output('F', $this->paramBag->get('app.game_pdf_directory').'YourQuest-'.$game->getSlug().'.pdf');

        // On retourne le PDF directement dans le visualiseur du navigateur
        return new Response($pdf->Output(), 200, array(
            'Content-Type' => 'application/pdf'));

        // On retourne le PDF en forçant son téléchargement 
        return new Response($pdf->Output('D', 'YourQuest-'.$game->getSlug().'.pdf'), 200, array(
            'Content-Type' => 'application/pdf'));
    }
}
