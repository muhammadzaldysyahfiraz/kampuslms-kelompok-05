<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\User;

class AssignmentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Assignment $assignment): bool
    {
        return $user->can('view', $assignment->course);
    }

    public function create(User $user, Course $course): bool
    {
        return $this->manages($user, $course);
    }

    public function update(User $user, Assignment $assignment): bool
    {
        return $this->manages($user, $assignment->course);
    }

    public function delete(User $user, Assignment $assignment): bool
    {
        return $this->manages($user, $assignment->course);
    }

    private function manages(User $user, Course $course): bool
    {
        return $user->role === 'admin'
            || ($user->role === 'dosen' && $course->isTaughtBy($user));
    }
}