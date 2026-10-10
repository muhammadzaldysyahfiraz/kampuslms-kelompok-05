<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\Submission;
use App\Models\User;

class SubmissionPolicy
{
    public function viewAny(User $user): bool
    {
        // Daftar disaring di query: dosen hanya MK sendiri, mahasiswa hanya miliknya.
        return true;
    }

    public function view(User $user, Submission $submission): bool
    {
        return match ($user->role) {
            'admin'     => true,
            'dosen'     => $submission->assignment->course->isTaughtBy($user),
            'mahasiswa' => $submission->user_id === $user->id,
            default     => false,
        };
    }

    public function create(User $user, Assignment $assignment): bool
    {
        return $user->role === 'mahasiswa'
            && $assignment->course->hasStudent($user);
    }

    // Keputusan kelompok (di luar matriks): admin dan dosen pemilik boleh mengubah/menghapus.
    public function update(User $user, Submission $submission): bool
    {
        return $this->managesAsStaff($user, $submission);
    }

    public function delete(User $user, Submission $submission): bool
    {
        return $this->managesAsStaff($user, $submission);
    }

    private function managesAsStaff(User $user, Submission $submission): bool
    {
        return $user->role === 'admin'
            || ($user->role === 'dosen'
                && $submission->assignment->course->isTaughtBy($user));
    }
}