<?php 

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\SessionService;
use App\Service\GithubService;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ProjectController extends AbstractController {

    #[Route('/projects', name: 'all_projects')]
    public function allProjects(GithubService $githubService, SessionService $sessionService): Response
    {
        $accessToken = $sessionService->getGithubData()['user']['access_token'] ?? null;
        $user = $githubService->apiRequest('https://api.github.com/user', $accessToken);

        $repos = $accessToken ? $githubService->apiRequest('https://api.github.com/user/repos', $accessToken) : [];
        $orgRepos = $githubService->apiRequest('https://api.github.com/orgs/Nyx-Corp/repos');
        $projects = array_merge($repos, $orgRepos);

        return $this->render('projects.html.twig', [
            'repositories' => $projects,
            'userProfile' => $user,
            'total_repos' => count($projects),
            'unread_notifications' => $githubService->apiRequest('https://api.github.com/notifications', $accessToken),
        ]);
    }

    #[Route('/project/{name}', name: 'project_details', requirements: ['name' => '.+'], methods: ['GET'])]
    public function projectDetails(string $name, GithubService $githubService, SessionInterface $session): Response
    {
        $userProfile = $session->get('github_user', []);
        if (empty($userProfile['access_token'])) {
            return new Response('Authentification requise.', Response::HTTP_FORBIDDEN);
        }

        $accessToken = $userProfile['access_token'];
        $owner = $userProfile['username'] ?? null;

        try {
            $projectDetails = $githubService->apiRequest("https://api.github.com/repos/{$owner}/{$name}", $accessToken);
            $tags = $githubService->apiRequest("https://api.github.com/repos/{$owner}/{$name}/tags", $accessToken);

            return $this->render('project_detail.html.twig', [
                'project' => $projectDetails,
                'versions' => $tags
            ]);
        } catch (\Exception $e) {
            return new Response('Erreur GitHub API : ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
