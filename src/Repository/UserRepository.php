<?php

namespace App\Repository;

use App\Entity\User;

class UserRepository
{
    private static array $users = [];

    public static function findOneByGithubId(string $githubId): ?User
    {
        return self::$users[$githubId] ?? null;
    }

    public static function save(User $user): void
    {
        self::$users[$user->getGithubId()] = $user;
    }

    public static function remove(User $user): void
    {
        unset(self::$users[$user->getGithubId()]);
    }

    public static function getAllUsers(): array
    {
        return array_values(self::$users);
    }
}
