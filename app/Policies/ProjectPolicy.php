<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Project $project): bool
    {
        return $this->canAccess($user, $project);
    }

    public function create(User $user): bool
    {
        return $user !== null;
    }

    public function update(User $user, Project $project): bool
    {
        return $this->canAccess($user, $project);
    }

    public function delete(User $user, Project $project): bool
    {
        return $this->canAccess($user, $project);
    }

    public function manageMembers(User $user, Project $project): bool
    {
        return $this->canAccess($user, $project);
    }

    protected function canAccess(User $user, Project $project): bool
    {
        if ($project->user_id === $user->id) {
            return true;
        }

        if ($project->members()->where('users.id', $user->id)->exists()) {
            return true;
        }

        $run = $project->challengeRun;

        if (! $run) {
            return false;
        }

        if ($run->owner_id === $user->id) {
            return true;
        }

        return $run->participantLinks()->where('user_id', $user->id)->exists();
    }
}
