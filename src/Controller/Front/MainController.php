<?php

namespace App\Controller\Front;

use App\Repository\GameRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/",name="front_")
 */
class MainController extends AbstractController
{    
    /**
     * @Route("/", name="main", methods={"GET"})
     */
    public function index(GameRepository $gameRepository, Request $request): Response
    {    
        $games = $gameRepository->findNextGame();

        return $this->render('front/main/index.html.twig', [
            'games' => $games,
        ]);
    }

    /**
     * @Route("/message", name="message", methods={"GET"})
     */
    public function message(GameRepository $gameRepository, Request $request): Response
    {    
        $games = $gameRepository->findBy(['isTrashed' => 0, 'status' => 1], ['createdAt' => 'ASC']);
       

        if ($this->getUser() == null) {
            /* Adding a flash message */
            $message = $this->addFlash(
                "notice-danger",
                "Vous devez avoir un compte \"organisateur\" pour pouvoir créer un jeu. Veuillez remplir ce formulaire d'inscription !"
            );

            return $this->redirectToRoute('app_register');

        } else if ($this->getUser() !== null && in_array("ROLE_JOUEUR", $this->getUser()->getRoles())) {
            /* It's adding a flash message */
            $this->addFlash(
                "notice-danger",
                "Vous devez avoir un compte \"organisateur\" pour pouvoir créer un jeu ! Pour cela vous pouvez nous contacter à l'adresse admin@yourquest.fr"
            );

            return $this->redirectToRoute('front_main');

        } else if ($this->getUser() !== null && in_array("ROLE_ORGANISATEUR", $this->getUser()->getRoles()) || in_array("ROLE_ADMIN", $this->getUser()->getRoles()) ) {
            return $this->redirectToRoute('app_backoffice_game_new');
        }        
    }

    /**
     * @Route("/cgu", name="cgu", methods={"GET"})
     */
    public function cgu(): Response
    {
        return $this->render('front/main/cgu.html.twig', []);
    }

    /**
     * @Route("/contact", name="contact", methods={"GET"})
     *
     * @return Response
     */
    public function contact(): Response
    {
        return $this->render('front/main/contact.html.twig', []);
    }

    /**
     * @Route("/contact-send", name="contact_send", methods={"POST"})
     */
    public function contact_send(MailerInterface $mailer, TranslatorInterface $translator): Response
    {
        $emailll = (new TemplatedEmail())
            ->from(new Address('sgeraudie@gmail.com', 'Your Quest'))
            ->to($_POST['email'])
            ->subject('Demande de contact YourQuest')
            ->htmlTemplate('front/main/_contact_email.html.twig')
            ->context([
                'name' => $_POST['name'],
                'subject' => $_POST['subject'],
                'message' => $_POST['message'],
                'contact_email' => $_POST['email']
            ])
        ;
        
        $mailer->send($emailll);

        $this->addFlash('notice-success', sprintf(
            'L\'email a été envoyé'
        ));
        return $this->render('front/main/contact.html.twig', []);
    }
}
