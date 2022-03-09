<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api", name="api_")
 */
class ApiController extends AbstractController
{
    /**
     * @Route("/users", name="list_users", methods={"GET"})
     */
    public function list_users(UserRepository $userRepos): Response
    {
        return $this->json(
            $userRepos->findAll(),
            // HTTP status code
            Response::HTTP_OK,
            // HTTP header
            [],
            // Contexte de serialization
            ['groups' => 'list_user']
        );
    }
}
