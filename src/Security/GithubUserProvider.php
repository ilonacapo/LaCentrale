<?php

namespace App\Security;

use App\Domain\User;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class GithubUserProvider implements UserProviderInterface
{
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $session = new Session();
        $githubData = $session->get('github_user', []);

        $user = new User();
        $user->setGithubId($identifier);

        if (isset($githubData['access_token'])) {
            $user->setAccessToken($githubData['access_token']);
        }

        if (isset($githubData['username'])) {
            $user->setUsername($githubData['username']);
        }

        if (!$user instanceof UserInterface) {
            throw new \LogicException('L\'utilisateur chargé n\'est pas une instance de UserInterface.');
        }

        return $user;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($user)));
        }

        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class;
    }
}
