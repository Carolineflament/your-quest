<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CheckpointController extends AbstractController
{
    /**
     * @Route("/qrscan", name="front_checkpoint", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render('front/checkpoint/index.html.twig', [
            'controller_name' => 'CheckpointController',
        ]);
    }
}
