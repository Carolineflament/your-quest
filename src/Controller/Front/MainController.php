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
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
     * @Route("/mentions-legales", name="mentions_legales", methods={"GET"})
     */
    public function mentions_legales(): Response
    {
        return $this->render('front/main/mentions_legales.html.twig', []);
    }

    /**
     * @Route("/contact", name="contact", methods={"GET", "POST"})
     *
     * @return Response
     */
    public function contact(Request $request, MailerInterface $mailer): Response
    {
        $this->addFlash(
            "notice-danger",
            "Vous devez être connecté pour nous envoyer un message."
        );

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $form = $this->createFormBuilder()
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'Votre nom : ',
                'attr' => ['placeholder' => 'Votre nom']
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'label' => 'Votre E-mail : ',
                'attr' => ['placeholder' => 'Votre E-mail']
            ])
            ->add('subject', TextType::class, [
                'required' => true,
                'label' => 'Sujet : ',
                'attr' => ['placeholder' => 'Sujet']
            ])
            ->add('message', TextareaType::class, [
                'required' => true,
                'label' => 'Laissez votre message : ',
                'attr' => ['placeholder' => 'Message', 'style' => 'height:100px']
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) 
        {
                $data = $form->getData();
                $email = (new TemplatedEmail())
                ->from(new Address('contact@yourquest.fr', 'Your Quest'))
                ->to($data['email'])
                ->subject('Demande de contact YourQuest')
                ->htmlTemplate('front/main/_contact_email.html.twig')
                ->context([
                    'name' => $data['name'],
                    'subject' => $data['subject'],
                    'message' => $data['message'],
                    'contact_email' => $data['email']
                ])
            ;
            
            $mailer->send($email);

            $this->addFlash('notice-success', sprintf(
                'L\'email a été envoyé'
            ));
        }

        return $this->render('front/main/contact.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
