<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('*', function ($view) {
            // Role akun login adalah sumber kebenaran; guest hanya mendapat tampilan default.
            $activeUser = auth()->user();
            $activeRole = $activeUser?->role ?? 'mahasiswa';

            if (!$activeUser) {
                if ($activeRole === 'dosen') {
                    $activeUser = User::where('role', 'dosen')->first();
                } elseif ($activeRole === 'admin') {
                    $activeUser = User::where('role', 'admin')->first();
                } else {
                    $activeUser = User::where('role', 'mahasiswa')->first();
                }

                if (!$activeUser) {
                    $activeUser = User::first() ?? (object) [
                        'name' => 'Muhammad Rifa Al-Rizqul',
                        'email' => '10241050@student.itk.ac.id',
                        'role' => 'mahasiswa',
                        'nim_nip' => '10241050',
                    ];
                }
            }

            $view->with('activeRole', $activeRole);
            $view->with('activeUser', $activeUser);
        });
    }
}
