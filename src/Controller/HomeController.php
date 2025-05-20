<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\SessionService;
use App\Service\GithubService;
use Exception;

class HomeController extends AbstractController
{


    #[Route('/', name: 'homepage')]
    public function index(GithubService $githubService, SessionService $sessionService): Response
    {

        $githubData = $sessionService->getGithubData();

        $user = $this->getUser();
        $accessToken = $githubData['user']['access_token'];


        if (!isset($githubData['user']['access_token']) || !is_string($githubData['user']['access_token']) || !$accessToken) {
            $userRepos = [];
            $nyxRepos = $githubService->apiRequest('https://api.github.com/orgs/Nyx-Corp/repos/type=public', $_ENV['GITHUB_TOKEN']);
            $projects =  $nyxRepos;
            return $this->render('home/index.html.twig', [
                'projects' => $projects,
                'organizations' => [],
                'is_logged_in' => false,
                'unread_notifications' => '0',

            ]);
        } else  {

        $accessToken = $githubData['user']['access_token'];


        $userRepos = $githubService->apiRequest('https://api.github.com/user/repos', $accessToken);
        $nyxRepos = $githubService->apiRequest('https://api.github.com/orgs/Nyx-Corp/repos', $accessToken);

        $projects = array_merge(array_slice($userRepos, 1), array_slice($nyxRepos, 2));
        return $this->render('home/index.html.twig', [
            'projects' => $projects,
            'organizations' => $githubService->apiRequest('https://api.github.com/user/orgs', $accessToken),
            'is_logged_in' => true,
            'unread_notifications' => $githubService->apiRequest('https://api.github.com/notifications', $accessToken),

        ]);
    }
    }


    #[Route('/profile', name: 'profile')]
    public function profile(SessionService $sessionService, GithubService $githubService): Response
    {
        $githubData = $sessionService->getGithubData();

        if (!isset($githubData['user']['access_token']) || !is_string($githubData['user']['access_token'])) {
            throw new Exception('Le jeton d\'accès est absent ou invalide.');
        }

        $accessToken = $githubData['user']['access_token'];

        try {
            $userProfile = $githubService->apiRequest('https://api.github.com/user', $accessToken);
            $repositories = $githubService->apiRequest('https://api.github.com/user/repos', $accessToken);
            $organizations = $githubService->apiRequest('https://api.github.com/user/orgs', $accessToken);

            if (!is_array($repositories)) {
                $repositories = [];
            }


            $allCommits = [];
            foreach ($repositories as $repo) {
                $commits = $githubService->apiRequest("https://api.github.com/repos/{$userProfile['login']}/{$repo['name']}/commits?per_page=5", $accessToken);
                if (!is_array($commits)) {
                    $commits = [];
                }
                foreach ($commits as $commit) {
                    $commit['repo_name'] = $repo['name'];
                    $allCommits[] = $commit;
                }
            }

            // Trier les commits par date (du plus récent au plus ancien)
            usort($allCommits, fn($a, $b) => strtotime($b['commit']['author']['date']) <=> strtotime($a['commit']['author']['date']));

            $latestCommits = array_slice($allCommits, 0, 5);

            return $this->render('profile.html.twig', [
                'userProfile' => $userProfile,
                'repositories' => $repositories,
                'organizations' => $organizations,
                'total_repos' => count($repositories),
                'latestCommits' => $latestCommits,
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
