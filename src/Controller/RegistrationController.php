<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\RoleRepository;
use App\Security\LoginFormAuthenticator;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/inscription", name="app_register")
     */
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, LoginFormAuthenticator $authenticator, EntityManagerInterface $entityManager, RoleRepository $roleRepos): Response
    {
        $user = new User();
        $user->setCreatedAt(new DateTimeImmutable('now'));
        
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        //dd($form);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
            $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            $route_redirect = "";
            if(($form->get('beOrganisateur')->getData()))
            {
                $role = $roleRepos->findOneBy(["slug" => "ROLE_ORGANISATEUR"]);
                $route_redirect = "";

                //TODO change route to game list in backoffice
                $route_redirect = "front_main";
            }
            else
            {
                $role = $roleRepos->findOneBy(["slug" => "ROLE_USER"]);
                $route_redirect = "front_main";
            }
            $user->setRole($role);
            $user->setStatus(true);
            
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email
            $this->addFlash(
                'notice-success',
                'Votre compte a été ajouté !'
            );

            return $userAuthenticator->authenticateUser(
                $user, 
                $authenticator, 
                $request); 
            
            return $this->redirectToRoute($route_redirect, [], Response::HTTP_SEE_OTHER);
            
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}