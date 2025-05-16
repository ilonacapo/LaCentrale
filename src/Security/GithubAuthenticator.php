<?php

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Psr\Log\LoggerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;

class GithubAuthenticator extends AbstractAuthenticator
{
    private ClientRegistry $clientRegistry;
    private RequestStack $requestStack;
    private LoggerInterface $logger;

    public function __construct(
        ClientRegistry $clientRegistry,
        LoggerInterface $logger,
        UserRepository $userRepository,
        RequestStack $requestStack
    ) {
        $this->clientRegistry = $clientRegistry;
        $this->logger = $logger;
        $this->requestStack = $requestStack;
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('github');
        $session = $this->requestStack->getSession();

        if (!$session->isStarted()) {
            $session->start();
        }

        $accessToken = $client->getAccessToken();
        $githubUser = $client->fetchUserFromToken($accessToken);


        $userData = $githubUser->toArray();
        $avatarUrl = $userData['avatar_url'] ?? null;

        $session->set('github_user', [
            'id' => $userData['id'] ?? null,
            'username' => $userData['login'] ?? null,
            'access_token' => $accessToken->getToken(),
            'avatar_url' => $avatarUrl,
        ]);

        return new SelfValidatingPassport(new UserBadge($userData['id']));
    }

    public function supports(Request $request): bool
    {
        return $request->attributes->get('_route') === 'connect_github_check';
    }

    public function start(Request $request): RedirectResponse
    {
        $session = $this->requestStack->getSession();


        if (!$session->isStarted()) {
            $session->start();
        }

        // Générer et stocker un state sécurisé
        $state = bin2hex(random_bytes(16));
        $session->set('oauth2_state', $state);
        $session->save();


        return $this->clientRegistry->getClient('github')->redirect([
            'state' => $state,
            'scope' => 'user,repo,notifications',
            'allow_signup' => false,
            'force_login' => true
        ], []);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $this->logger->warning('Échec d\'authentification', ['exception' => $exception->getMessage()]);
        return new Response('Authentification échouée : ' . $exception->getMessage(), Response::HTTP_FORBIDDEN);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse('/profile');
    }
}
