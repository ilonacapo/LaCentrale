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
use Psr\Log\LoggerInterface;

class GithubController extends AbstractController
{
    private DeploymentService $deploymentService;
    private LoggerInterface $logger;


    public function __construct(DeploymentService $deploymentService, LoggerInterface $logger)
    {
        $this->logger = $logger;

        $this->deploymentService = $deploymentService;
    }

    #[Route('/deploy', name: 'deploy_version', methods: ['POST'])]
    public function deployVersion(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $repoName = $data['repoName'] ?? null;
        $version = $data['version'] ?? null;
        $baseDir = $data['baseDir'] ?? null;
        try {
            $this->logger->info("Tentative de déploiement : Repo = $repoName, Version = $version, BaseDir = $baseDir");

            $result = $this->deploymentService->deploy($repoName, $version, $baseDir);

            return new JsonResponse($result);
        } catch (\Exception $e) {
            $this->logger->error("Erreur interne : " . $e->getMessage());
            return new JsonResponse(['success' => false, 'message' => 'Erreur interne du serveur', 'error' => $e->getMessage()], 500);
        }
        return new JsonResponse($result);
    }

    #[Route('/deploy/logs', name: 'deploy_logs')]
    public function streamLogs()
    {
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');

        $logFile = '/var/log/deploy.log';

        while (true) {
            if (file_exists($logFile)) {
                $logs = explode("\n", file_get_contents($logFile));
                $lastLogs = array_slice($logs, -2);

                echo "data: " . json_encode(['logs' => nl2br(implode("\n", $lastLogs))]) . "\n\n";
                ob_flush();
                flush();
            }
            sleep(1); // Pause avant de récupérer les nouveaux logs
        }
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
