<?php

namespace App\Policies;

use App\Models\Grade;
use App\Models\Submission;
use App\Models\User;

class GradePolicy
{
    public function viewAny(User $user): bool
    {
        // Disaring di query: dosen hanya MK sendiri, mahasiswa hanya nilainya.
        return true;
    }

    public function view(User $user, Grade $grade): bool
    {
        // Catatan kelompok: tidak ada kolom "dipublikasikan" di tabel grades,
        // jadi nilai yang sudah ada dapat dilihat mahasiswa pemiliknya.
        return match ($user->role) {
            'admin'     => true,
            'dosen'     => $grade->submission->assignment->course->isTaughtBy($user),
            'mahasiswa' => $grade->submission->user_id === $user->id,
            default     => false,
        };
    }

    public function create(User $user, Submission $submission): bool
    {
        return $user->role === 'dosen'
            && $submission->assignment->course->isTaughtBy($user);
    }

    public function update(User $user, Grade $grade): bool
    {
        return $user->role === 'dosen'
            && $grade->submission->assignment->course->isTaughtBy($user);
    }

    // Keputusan kelompok (di luar matriks): dosen pemilik boleh menghapus nilai.
    public function delete(User $user, Grade $grade): bool
    {
        return $this->update($user, $grade);
    }
}