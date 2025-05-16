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
            return null;
        }

        
            $headers = ['Accept' => 'application/vnd.github.v3+json', 'User-Agent' => 'SymfonyHttpClient'];
            if ($accessToken !== null) {
                $headers['Authorization'] = 'Bearer ' . $accessToken;
            }

            $response = $this->httpClient->request('GET', $endpoint, ['headers' => $headers]);

            if ($response->getStatusCode() !== 200) {
                return null;
            }

            $data = $response->toArray();

            return $data;
    }

    public function getUserProfile($user): ?array
    {
        return $this->apiRequest('https://api.github.com/user', $user->getAccessToken());
    }
}
