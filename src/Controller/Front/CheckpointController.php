<?php

namespace App\Controller\Front;

use App\Entity\Checkpoint;
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

    /**
     * @Route("/check/{id}/{token}", name="front_checkpoint_check", methods={"GET", "POST"}, requirements={"id"="\d+"})
     */
    public function check(Checkpoint $checkpoint, string $token) : Response
    {


        // TODO check si token correspond bien au checkpoint

        //TODO cehck si user bien connecté, sinon page de login + session url pour retourner ici

        // TODO Test si l'utilisateur en est bien à se check point

        // TODO si 1er check point test si l'utilisateur à lancé le round sinon on lance

        // TODO si pas 1er checkpoint et utilisateur n'a pas lancé la partie erreur

        // TODO mettre entrée dans le scanat + affichage message success

        // TODO si dernier checkpoint on ferme le round
        return $this->render('front/checkpoint/check.html.twig', [
            'controller_name' => 'CheckpointController',
        ]);
    }
}
