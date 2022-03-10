<?php

namespace App\Controller\Front;

use App\Entity\Game;
use App\Entity\Instance;
use App\Form\RegistrationFormType;
use App\Form\UserType;
use App\Repository\GameRepository;
use App\Repository\InstanceRepository;
use App\Repository\RoundRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user/",name="app_front_user_")
 */
class UserController extends AbstractController
{
    /**
     * @Route("profil", name="profile", methods={"GET", "POST"})
     */
    public function profile(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, RoundRepository $roundRepository, GameRepository $gameRepository, InstanceRepository $instanceRepository)
    {
        $user = $this->getUser();

        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('password')->getData() !== null) {
                $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));
            }
            $entityManager->flush();

            $this->addFlash(
                'notice-success',
                'Votre profil a été mis à jour !'
            );

            return $this->redirectToRoute('app_front_user_profile', [], Response::HTTP_SEE_OTHER);
        }
        
        // It's getting the user id of the user connected.
        $userConnected = $this->getUser()->getId();

        return $this->renderForm('front/user/profile.html.twig', [
            'user' => $user,
            'form' => $form,
            'rounds' => $roundRepository->findBy(['user' => $userConnected]),
        ]);
    }

}
