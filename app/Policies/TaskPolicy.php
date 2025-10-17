<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Task $task): bool
    {
        return $this->canAccess($user, $task);
    }

    public function create(User $user, Project $project): bool
    {
        return (new ProjectPolicy())->view($user, $project);
    }

    public function update(User $user, Task $task): bool
    {
        return $this->canAccess($user, $task);
    }

    public function delete(User $user, Task $task): bool
    {
        return $this->canAccess($user, $task);
    }

    public function comment(User $user, Task $task): bool
    {
        return $this->canAccess($user, $task);
    }

    protected function canAccess(User $user, Task $task): bool
    {
        $project = $task->project;

        if (! $project) {
            return false;
        }

        return (new ProjectPolicy())->view($user, $project);
    }
}
