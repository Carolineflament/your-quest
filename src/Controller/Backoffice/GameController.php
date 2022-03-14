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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/back/jeux", name="app_backoffice_")
 */
class GameController extends AbstractController
{
    private $paramBag;
    private $urlGenerator;
    private $breadcrumb;

    public function __construct(ParameterBagInterface $paramBag, UrlGeneratorInterface $urlGenerator)
    {
        $this->paramBag = $paramBag;
        $this->urlGenerator = $urlGenerator;
        $this->breadcrumb = array(array('libelle' => 'Jeux', 'libelle_url' => 'app_backoffice_game_index', 'url' => $this->urlGenerator->generate('app_backoffice_game_index')));
    }
    /**
     * @Route("/", name="game_index", methods={"GET"})
     */
    public function index(GameRepository $gameRepository): Response
    {
        // It's getting the user id of the user connected.
        $userConnected = $this->getUser()->getId();

        if(in_array('ROLE_ADMIN', $this->getUser()->getRoles()))
        {
            $show_active_games = $gameRepository->findBy(['status' => 1, 'isTrashed' => 0], ['createdAt' => 'DESC']);
            $show_inactive_games = $gameRepository->findBy(['status' => 0, 'isTrashed' => 0], ['createdAt' => 'DESC']);
        }
        else
        {
            $show_active_games = $gameRepository->findBy(['status' => 1, 'isTrashed' => 0, 'user' => $userConnected], ['createdAt' => 'DESC']);
            $show_inactive_games = $gameRepository->findBy(['status' => 0, 'isTrashed' => 0, 'user' => $userConnected], ['createdAt' => 'DESC']);
        }

        return $this->render('backoffice/game/index.html.twig', [
            'actives_games' => $show_active_games,
            'inactives_games' => $show_inactive_games,
            'breadcrumbs' => $this->breadcrumb
        ]);
    }

    /**
     * @Route("/nouveau", name="game_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager, MySlugger $mySlugger): Response
    {
        $game = new Game();
        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $game->setUser($this->getUser());
            $file = $form['image']->getData();
            if($file !== null)
            {
                $slug = $mySlugger->slugify($game->getTitle(), Game::class);
                $filename = $slug.'.'.$file->guessExtension();
                $file->move($this->paramBag->get('app.game_images_directory'), $filename);
                $game->setImage($filename);
            }
            $entityManager->persist($game);
            $entityManager->flush();

            $this->addFlash(
                'notice-success',
                'Le jeu '.$game->getTitle().' a été créé !'
            );

            return $this->redirectToRoute('app_backoffice_game_show', ['slug' => $game->getSlug()], Response::HTTP_SEE_OTHER);
        }

        array_push($this->breadcrumb, array('libelle' => 'Nouveau jeu', 'libelle_url' => 'app_backoffice_game_new', 'url' => $this->urlGenerator->generate('app_backoffice_game_new')));

        return $this->renderForm('backoffice/game/new.html.twig', [
            'game' => $game,
            'form' => $form,
            'breadcrumbs' => $this->breadcrumb,
        ]);
    }

    /**
     * @Route("/{slug}", name="game_show", methods={"GET"})
     */
    public function show(Game $game): Response
    {
        // Organizer or Admin can modify this game
        $this->denyAccessUnlessGranted('IS_MY_GAME', $game);
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

        array_push($this->breadcrumb, array('libelle' => $game->getTitle(), 'libelle_url' => 'app_backoffice_game_show', 'url' => $this->urlGenerator->generate('app_backoffice_game_show', ['slug' => $game->getSlug()])));

        return $this->render('backoffice/game/show.html.twig', [
            'game' => $game,
            'instances' => $instances,
            'breadcrumbs' => $this->breadcrumb,
        ]);
    }

    /**
     * @Route("/{slug}/modifier", name="game_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Game $game, EntityManagerInterface $entityManager, MySlugger $mySlugger): Response
    {
        // Organizer or Admin can modify this game
        $this->denyAccessUnlessGranted('IS_MY_GAME', $game);

        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file = $form['image']->getData();
            if($file !== null)
            {
                $slug = $mySlugger->slugify($game->getTitle(), Game::class, $game->getId());
                $filename = $slug.'.'.$file->guessExtension();
                $file->move($this->paramBag->get('app.game_images_directory'), $filename);
                $game->setImage($filename);
            }

            $entityManager->flush();

            $this->addFlash(
                'notice-success',
                'Le jeu '.$game->getTitle().' a été modifié !'
            );

            return $this->redirectToRoute('app_backoffice_game_show', ['slug' => $game->getSlug()], Response::HTTP_SEE_OTHER);
        }

        array_push($this->breadcrumb, array('libelle' => $game->getTitle(), 'libelle_url' => 'app_backoffice_game_edit', 'url' => $this->urlGenerator->generate('app_backoffice_game_edit', ['slug' => $game->getSlug()])));

        return $this->renderForm('backoffice/game/edit.html.twig', [
            'game' => $game,
            'form' => $form,
            'breadcrumbs' => $this->breadcrumb,
        ]);
    }

    /**
     * @Route("/{id}", name="game_delete", methods={"POST"}, requirements={"id"="\d+"})
     */
    public function delete(Request $request, Game $game, CascadeTrashed $cascadeTrashed): Response
    {
        // Organizer or Admin can modify this game
        $this->denyAccessUnlessGranted('IS_MY_GAME', $game); 

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
     * @Route("/status/{id}",name="update_status", methods={"POST"}, requirements={"id"="\d+"})
     *
     * @param Game $game
     * @return void
     */
    public function update_status(Request $request, Game $game, EntityManagerInterface $entityManager)
    {
        // Organizer or Admin can modify this game
        $this->denyAccessUnlessGranted('IS_MY_GAME', $game);

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
     * @Route("/{slug}/pdf", name="game_pdf", methods={"GET"})
     * 
     * @return void
     */
    public function pdf(Game $game): Response
    {
        // Organizer or Admin can modify this game
        $this->denyAccessUnlessGranted('IS_MY_GAME', $game);
        
        /***** On prépare les données à insérer dans le PDF *****/

        // Titre des pages
        $title = $game->getTitle();
        // On converti de utf-8 vers ISO-8859-1 pour gérer les accents
        $title = utf8_decode($title);

        /***** On génére le document PDF *****/

        // On récupère la liste des checkpoints non mis à la poubelle
        // et dans l'ordre choisi par l'utilisateur grâce à @ORM\OrderBy({"orderCheckpoint" = "ASC"}) sur la propriété dans l'entité.
        $checkpointsList = $game->getUnTrashedCheckpoints();

        // Création d'un nouvel objet (document PDF)
        $pdf = new \FPDF();

        $i = 1;

        // On boucle sur la liste des checkpoints
        foreach ($checkpointsList as $checkpoint) {
            // Ajout d'une nouvelle page, avec ses header et footer
            $pdf->AddPage();

            // Réglage de la police du titre
            $pdf->SetFont('Arial', 'B', 30);
            // Titre en haut de page, dans une cellule avec passage à la ligne et création d'une nouvelle cellule si trop long (MultiCell)
            $pdf->MultiCell(0, 10, $title, 0, 'C');
            // Saut de ligne
            $pdf->Ln(10);

            // Réglage de la police du numéro d'ordre du checkpoint
            $pdf->SetFont('Arial', 'B', 45);
            // Numéro d'ordre du checkpoint
            $pdf->MultiCell(0, 10, 'CHECKPOINT '.$i , 0, 'C');
            // Saut de ligne
            $pdf->Ln(25);
            // Increment
            $i++;

            // Déplacement du curseur sur axe X pour centrage du QR code
            $pdf->SetX(45);
            // Insertion du QR code
            $pdf->Image($this->paramBag->get('app.game_qrcode_directory').$checkpoint->getId().'qrcode.png', null, null, 120);
            // Saut de ligne
             $pdf->Ln(25);

            // Réglage de la police du nom du checkpoint
            $pdf->SetFont('Arial', 'B', 30);
            // Nom du checkpoint
            $pdf->MultiCell(0, 10, $checkpoint->getTitle(), 0, 'C');
            
        }

        /***** On traite le document PDF généré *****/

        // On enregistre le PDF dans le dossier "pdf"
        // $pdf->Output('F', $this->paramBag->get('app.game_pdf_directory').'YourQuest-'.$game->getSlug().'.pdf');

        // On retourne le PDF directement dans le visualiseur du navigateur
        return new Response($pdf->Output(), 200, array(
            'Content-Type' => 'application/pdf'));

        // On retourne le PDF en forçant son téléchargement 
        // return new Response($pdf->Output('D', 'YourQuest-'.$game->getSlug().'.pdf'), 200, array(
        //     'Content-Type' => 'application/pdf'));
    }
}
