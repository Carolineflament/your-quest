<?php

namespace App\Security;

use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private UrlGeneratorInterface $urlGenerator;
    private UserRepository $userRepository;
    private SessionInterface $session;
    private UserInterface $userInterface;
    private JWTTokenManagerInterface $JWTManager;

    public function __construct(UrlGeneratorInterface $urlGenerator, UserRepository $userRepository, SessionInterface $session, JWTTokenManagerInterface $JWTManager)
    {
        $this->urlGenerator = $urlGenerator;
        $this->userRepository = $userRepository;
        $this->session = $session;
        $this->JWTManager = $JWTManager;
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email', '');

        $request->getSession()->set(Security::LAST_USERNAME, $email);

        $user_test = $this->userRepository->findOneBy(['email' => $email]);
        if(!$user_test->getStatus())
        {
            $request->getSession()->getFlashBag()->add(
                'notice-danger',
                'Votre utilisateur a été désactivé par un administrateur, vous pouvez conctacter un administrateur via le formulaire de contact'
            );
            
            return new Passport(new UserBadge(''), new PasswordCredentials(''));
        }
            
        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        $this->session->set('token', $this->JWTManager->create($token->getUser()));
        
        if($this->session->get('route_redirect') !== null)
        {
            return new RedirectResponse($this->session->get('route_redirect'));
        }
        elseif ($token->getUser()->getRole()->getSlug() === "ROLE_ADMIN")
        {
            return new RedirectResponse($this->urlGenerator->generate('app_admin_user_index'));
        }
        elseif($token->getUser()->getRole()->getSlug() === "ROLE_ORGANISATEUR")
        {
            return new RedirectResponse($this->urlGenerator->generate('app_backoffice_game_index'));
        }
        else
        {
            return new RedirectResponse($this->urlGenerator->generate('front_main'));
        }
        //throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
