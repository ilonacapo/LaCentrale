<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class GithubService
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function apiRequest(string $endpoint, ?string $accessToken = null): ?array
    {
        $accessToken = $accessToken ?? $_ENV['GITHUB_ACCESS_TOKEN'] ?? null;

        if ($accessToken !== null && (!is_string($accessToken) || empty($accessToken))) {
            file_put_contents(__DIR__ . '/../../public/debug.log', "Erreur : Jeton d'accès invalide\n", FILE_APPEND);
            return null; 
        }

        try {
            $headers = ['Accept' => 'application/vnd.github.v3+json', 'User-Agent' => 'SymfonyHttpClient'];
            if ($accessToken !== null) {
                $headers['Authorization'] = 'Bearer ' . $accessToken;
            }

            $response = $this->httpClient->request('GET', $endpoint, ['headers' => $headers]);

            if ($response->getStatusCode() !== 200) {
                file_put_contents(__DIR__ . '/../../public/debug.log', "Erreur API GitHub ($endpoint) : " . $response->getContent(false) . "\n", FILE_APPEND);
                return null;
            }

            $data = $response->toArray();
            file_put_contents(__DIR__ . '/../../public/debug.log', "Réponse GitHub ($endpoint) : " . print_r($data, true) . "\n", FILE_APPEND);

            return $data;
        } catch (TransportExceptionInterface $e) {
            file_put_contents(__DIR__ . '/../../public/debug.log', "Exception API GitHub ($endpoint) : " . $e->getMessage() . "\n", FILE_APPEND);
            return null;
        }
    }

    public function getUserProfile($user): ?array
    {
        return $this->apiRequest('https://api.github.com/user', $user->getAccessToken());
    }
}
