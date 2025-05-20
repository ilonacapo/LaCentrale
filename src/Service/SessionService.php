<?php

namespace App\Service;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionService
{
    private $session;

public function __construct(RequestStack $requestStack)
{
    $this->session = $requestStack->getSession();

    if (!$this->session->isStarted() && php_sapi_name() !== 'cli') {
        $this->session->start();
    }
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

        $this->session->set('github_repositories', $repositories);
        $this->session->set('github_organizations', $organizations);

        // Vérifier si les données sont bien stockées
        if (!$this->session->has('github_repositories')) {
           throw new \Exception('Les repositories n\'ont pas été stockés dans la session.');
        }

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

    public function getSession(): SessionInterface
{
    return $this->session;
}

}