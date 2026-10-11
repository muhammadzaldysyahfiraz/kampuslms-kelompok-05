<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CoursePolicy
{
    public function viewAny(User $user): bool
    {
        // Semua yang login boleh melihat daftar; isinya disaring per peran di query.
        return in_array($user->role, ['admin', 'dosen', 'mahasiswa'], true);
    }

    public function view(User $user, Course $course): Response
    {
        $allowed = match ($user->role) {
            'admin'     => true,
            'dosen'     => $course->isTaughtBy($user),
            'mahasiswa' => $course->hasStudent($user),
            default     => false,
        };

        return $allowed
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses ke sumber daya ini.');
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $user, Course $course): bool
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, Course $course): bool
    {
        return $user->role === 'admin';
    }

    /** Admin or the assigned lecturer may manage course content. */
    public function manageContent(User $user, Course $course): bool
    {
        return $user->role === 'admin'
            || ($user->role === 'dosen' && $course->isTaughtBy($user));
    }
}
