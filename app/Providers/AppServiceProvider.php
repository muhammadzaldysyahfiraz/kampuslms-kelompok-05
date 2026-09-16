<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $activeRole = session('active_role', 'mahasiswa');

            if ($activeRole === 'dosen') {
                $activeUser = User::where('role', 'dosen')->first();
            } elseif ($activeRole === 'admin') {
                $activeUser = User::where('role', 'admin')->first();
            } else {
                $activeUser = User::where('role', 'mahasiswa')->first();
            }

            if (!$activeUser) {
                $activeUser = User::first() ?? (object)[
                    'name' => 'Muhammad Rifa Al-Rizqul',
                    'email' => '10241050@student.itk.ac.id',
                    'role' => 'mahasiswa',
                    'nim_nip' => '10241050'
                ];
            }

            $view->with('activeRole', $activeRole);
            $view->with('activeUser', $activeUser);
        });
    }
}

