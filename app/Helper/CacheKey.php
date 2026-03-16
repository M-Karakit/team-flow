<?php

namespace App\Helper;

class CacheKey
{
    public static function userProjects(int $userId): string {
        return "user.{$userId}.projects";
    }

    public static function projectStats(int $projectId): string {
        return "project.{$projectId}.stats";
    }

    public static function projectTasks(int $projectId): string {
        return "project.{$projectId}.tasks";
    }
}
