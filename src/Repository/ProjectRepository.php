<?php

namespace App\Repository;

use App\Entity\Project;

class ProjectRepository
{
    private static array $projects = [];

    public static function findOneById(int $id): ?Project
    {
        return self::$projects[$id] ?? null;
    }

    public static function save(Project $project): void
    {
        self::$projects[$project->getId()] = $project;
    }

    public static function remove(Project $project): void
    {
        unset(self::$projects[$project->getId()]);
    }

    public static function getAllProjects(): array
    {
        return array_values(self::$projects);
    }
}
