<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class SessionService
{
    private $session;

    public function __construct(RequestStack $requestStack)
    {
        $this->session = $requestStack->getSession();
    }

    public function setGithubData(array $userData): void
    {
        $this->session->set('github_user', $userData);

        // Déboguer les données stockées
        if (!$this->session->has('github_user')) {
            throw new \Exception('Les données utilisateur n\'ont pas été stockées dans la session.');
        }
    }

    public function setRepositoriesAndOrganizations(array $repositories, array $organizations): void
    {
        // Log des données reçues
        file_put_contents('debug.log', "Repositories: " . print_r($repositories, true) . "\n", FILE_APPEND);
        file_put_contents('debug.log', "Organizations: " . print_r($organizations, true) . "\n", FILE_APPEND);

        $this->session->set('github_repositories', $repositories);
        $this->session->set('github_organizations', $organizations);

        // Vérifier si les données sont bien stockées
        if (!$this->session->has('github_repositories')) {
           // throw new \Exception('Les repositories n\'ont pas été stockés dans la session.');
        }

        file_put_contents('debug.log', "Repositories stockés dans la session : " . print_r($repositories, true) . "\n", FILE_APPEND);
        file_put_contents(__DIR__ . '/../../public/debug.log', "Repositories stockés dans la session : " . print_r($repositories, true) . "\n", FILE_APPEND);
    }

    public function getGithubData(): array
    {
        $githubUser = $this->session->get('github_user', []);
        $repositories = $this->session->get('github_repositories', []);
        $organizations = $this->session->get('github_organizations', []);

        if (!is_array($githubUser) || !is_array($repositories) || !is_array($organizations)) {
            throw new \LogicException('Les données récupérées depuis la session ne sont pas valides.');
        }

        $githubData = $this->session->get('github_user', []);

        if (empty($githubData['access_token'])) {
            throw new \LogicException('Le jeton d\'accès est absent de la session.');
        }

        return [
            'user' => $githubUser,
            'repositories' => $repositories,
            'organizations' => $organizations,
        ];
    }
}