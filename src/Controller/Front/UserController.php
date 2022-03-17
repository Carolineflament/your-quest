<?php

namespace App\Controller\Front;

use App\Entity\Game;
use App\Entity\Round;
use App\Form\UserType;
use App\Entity\Instance;
use App\Form\RegistrationFormType;
use App\Repository\GameRepository;
use App\Repository\UserRepository;
use App\Repository\RoundRepository;
use App\Repository\InstanceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @Route("/user/",name="app_front_user_")
 */
class UserController extends AbstractController
{
    /**
     * @Route("profil", name="profile", methods={"GET", "POST"})
     */
    public function profile(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, RoundRepository $roundRepository, ParameterBagInterface $paramBag)
    {
        $user = $this->getUser();

        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('password')->getData() !== null) {
                $user->setPassword($passwordHasher->hashPassword($user, $form->get('password')->getData()));
            }

            $file = $form['image']->getData();
            if($file !== null)
            {
                $pseudo = $user->getPseudo();
                $id = $user->getId();
                $filename = $pseudo.'-'. $id.'.'.$file->guessExtension();
                $file->move($paramBag->get('app.profile_images_directory'), $filename);
                $user->setImage($filename);
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
        $rounds = $roundRepository->findBy(['user' => $userConnected], ['endAt' => 'ASC']);

        return $this->renderForm('front/user/profile.html.twig', [
            'user' => $user,
            'form' => $form,
            'rounds' => $rounds,
        ]);
    }

}
