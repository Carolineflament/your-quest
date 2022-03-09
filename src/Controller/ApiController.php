<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * @Route("/api", name="api_")
 */
class ApiController extends AbstractController
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @Route("/users", name="list_users", methods={"GET"})
     */
    public function list_users(Request $request, UserRepository $userRepos, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        $users = $userRepos->findAllArray();
        
        foreach($users AS $key => $user)
        {
            $userBdd = $userRepos->find($user['id']);
            $users[$key]['role'] = $userBdd->getRole()->getName();
            $token = $csrfTokenManager->getToken('delete'.$user['id'])->getValue();
            $updateUrl = $this->urlGenerator->generate('app_admin_user_edit', ['id' => $user['id']]);
            $deleteUrl = $this->urlGenerator->generate('app_admin_user_update_status', ['id' => $user['id']]);
            $showUrl = $this->urlGenerator->generate('app_admin_user_show', ['id' => $user['id']]);

            $users[$key]['email'] = '<a href="'.$showUrl.'">'.$user['email'].'</a>';
            $users[$key]['status'] = $user['status'] ? 'Actif' : 'Inactif';
            $users[$key]['createdAt'] = $user['createdAt'] ? date('d-m-Y à H:i:s', date_timestamp_get($user['createdAt'])) : '';
            $users[$key]['updatedAt'] = $user['updatedAt'] ? date('d-m-Y à H:i:s', date_timestamp_get($user['updatedAt'])) : '';
            $users[$key]['actions'] = '<div class="d-grid gap-2 justify-content-md-center">
                <a href="'.$updateUrl.'" class="btn btn-primary">Modifier</a>
                <form method="post" action="'.$deleteUrl.'" ';
            if($user['status'])
            {
                $users[$key]['actions'] .= 'onsubmit="return confirm(\'Êtes-vous sûr de vouloir désactiver l\\\'utilisateur '.$user['email'].' ?\nSes jeux / checkpoints / questions seront mis à la poubelle !\');"';
            }
            $users[$key]['actions'] .= '>
                <input type="hidden" name="_token" value="'.$token.'">
                <button type="submit" class="btn ';

            if($user['status'])
            {
                $users[$key]['actions'] .= 'btn-danger';
            }
            else
            {
                $users[$key]['actions'] .= 'btn-success';
            }
            $users[$key]['actions'] .= '">';

            if($user['status'])
            {
                $users[$key]['actions'] .= 'Désactiver';
            }
            else
            {
                $users[$key]['actions'] .= 'Activer';
            }
            $users[$key]['actions'] .= '</button>
                </form>
            </div>';
        }
        //dd($users);
        return $this->json(
            $users,
            // HTTP status code
            Response::HTTP_OK,
            // HTTP header
            [],
            // Contexte de serialization
            ['groups' => 'list_users']
        );
    }
}
