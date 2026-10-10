<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\Material;
use App\Models\User;

class MaterialPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Material $material): bool
    {
        // Aturan akses materi = aturan akses mata kuliahnya.
        return $user->can('view', $material->course);
    }

    public function create(User $user, Course $course): bool
    {
        return $this->manages($user, $course);
    }

    public function update(User $user, Material $material): bool
    {
        return $this->manages($user, $material->course);
    }

    public function delete(User $user, Material $material): bool
    {
        return $this->manages($user, $material->course);
    }

    private function manages(User $user, Course $course): bool
    {
        // Cek peran dulu (murah), baru relasi.
        return $user->role === 'admin'
            || ($user->role === 'dosen' && $course->isTaughtBy($user));
    }
}