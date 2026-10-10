<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    public function viewAny(User $user): bool
    {
        // Semua yang login boleh melihat daftar; isinya disaring per peran di query.
        return true;
    }

    public function view(User $user, Course $course): bool
    {
        return match ($user->role) {
            'admin'     => true,
            'dosen'     => $course->isTaughtBy($user),
            'mahasiswa' => $course->hasStudent($user),
            default     => false,
        };
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $user, Course $course): bool
    {
        // Sesuai matriks: dosen tidak boleh mengubah mata kuliah hanya karena ia mengajarnya.
        return $user->role === 'admin';
    }

    public function delete(User $user, Course $course): bool
    {
        return $user->role === 'admin';
    }
}