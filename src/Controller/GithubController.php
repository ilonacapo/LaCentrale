<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use App\Service\DeploymentService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GithubController extends AbstractController
{
    private DeploymentService $deploymentService;

    public function __construct(DeploymentService $deploymentService)
    {
        $this->deploymentService = $deploymentService;
    }

    #[Route('/deploy', name: 'deploy_version', methods: ['POST'])]
    public function deployVersion(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $repoName = $data['repoName'] ?? null;
        $version = $data['version'] ?? null;
        $base_dir = $data['baseDir'] ?? null;

        if (!$repoName || !$version || !$base_dir) {
            return new JsonResponse(['success' => false, 'message' => "Données manquantes"], 400);
        }

        $result = $this->deploymentService->deploy($repoName, $version, $base_dir);

        return new JsonResponse($result);
    }

    #[Route('/connect/github', name: 'connect_github')]
    public function connectGithub(ClientRegistry $clientRegistry): Response
    {
        return $clientRegistry->getClient('github')->redirect([], []);
    }

    #[Route('/login/github/check', name: 'connect_github_check')]
    public function connectCheck(SessionService $sessionService, SessionInterface $session): Response
    {
        $user = $this->getUser();

        if (!$user || !$user->getAccessToken()) {
            return new Response('Authentification échouée ou jeton manquant.', Response::HTTP_FORBIDDEN);
        }

        $sessionService->setGithubData([
            'id' => $user->getGithubId(),
            'username' => $user->getUsername(),
            'email' => $user->getGithubId(),
            'access_token' => $user->getAccessToken(),
            'avatar_url' => $user->getAvatarUrl(),
        ]);

        // Vérifier si les données sont bien enregistrées
        $session->set('github_user', [
            'id' => $user->getGithubId(),
            'username' => $user->getUsername(),
            'email' => $user->getGithubId(),
            'access_token' => $user->getAccessToken(),
            'avatar_url' => $user->getAvatarUrl(),
        ]);

        return $this->redirectToRoute('profile');
    }
}
