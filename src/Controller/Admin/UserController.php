<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Service\CascadeTrashed;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @Route("/admin/user", name="app_admin_user_")
 * @IsGranted("ROLE_ADMIN") 
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="index", methods={"GET"})
     */
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('admin/user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash(
                'notice-success',
                'L\'utilisateur '.$user->getEmail().' a été ajouté !'
            );

            return $this->redirectToRoute('app_admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="show", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function show(User $user): Response
    {
        return $this->render('admin/user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="edit", methods={"GET", "POST"}, requirements={"id"="\d+"})
     */
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('password')->getData() !== null) {
                $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));
            }
            $entityManager->flush();

            $this->addFlash(
                'notice-success',
                'L\'utilisateur '.$user->getEmail().' a été modifié !'
            );

            return $this->redirectToRoute('app_admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/status/{id}",name="update_status", methods={"POST"}, requirements={"id"="\d+"})
     *
     * @param User $user
     * @return void
     */
    public function update_status(Request $request, User $user, EntityManagerInterface $entityManager, CascadeTrashed $cascadeTrashed)
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            if ($user->getStatus()) {
                foreach ($user->getGames() as $game) {
                    $cascadeTrashed->trashGame($game);
                }
                $user->setStatus(false);
                $this->addFlash(
                    'notice-success',
                    'L\'utilisateur '.$user->getEmail().' a été désactivé ! Tous ses jeux, checkpoints, questions et instances ont été mis à la poubelle !'
                );
            } else {
                $user->setStatus(true);
                $this->addFlash(
                    'notice-success',
                    'L\'utilisateur '.$user->getEmail().' a été activé !'
                );
            }

            $entityManager->flush();
        }
        else
        {
            $this->addFlash(
                'notice-danger',
                'Impossible de désactiver l\'utilisateur '.$user->getEmail().', token invalide !'
            );
        }
        return $this->redirectToRoute('app_admin_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
