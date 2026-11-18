<?php

namespace App\Providers;

use App\Models\Grade;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
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
        // Di lingkungan pengembangan, akses relasi tanpa eager loading melempar
        // exception (N+1 jadi terlihat). Di produksi tetap berjalan agar pengguna
        // tidak melihat halaman rusak.
        Model::preventLazyLoading(! app()->isProduction());

        // Role akun login adalah sumber kebenaran. Tamu TIDAK boleh memicu query
        // DB apa pun di sini — sebelumnya setiap render halaman publik
        // (mis. /tentang) menjalankan User::where('role', ...)->first(),
        // sehingga tamu bisa melihat tautan "Nilai Saya" (role mahasiswa) atau
        // "Pengguna" (role admin) yang bukan haknya, dan halaman error ikut
        // menanggung query tersebut.
        View::composer('*', function ($view) {
            $activeUser = auth()->user();
            $activeRole = $activeUser?->role ?? 'guest';

            $view->with('activeRole', $activeRole);
            $view->with('activeUser', $activeUser);
        });

        // Satu definisi navigasi untuk desktop & mobile; hak akses dicek lewat Policy.
        View::composer('components.layout', function ($view) {
            $activeRole = auth()->user()?->role ?? 'guest';

            $navItems = array_values(array_filter([
                [
                    'label' => 'Dashboard',
                    'url' => route('dashboard'),
                    'active' => request()->routeIs('dashboard'),
                    'visible' => true,
                ],
                [
                    'label' => $activeRole === 'dosen' ? 'Mata Kuliah Diampu' : 'Mata Kuliah',
                    'url' => route('courses.index'),
                    'active' => request()->routeIs('courses.*'),
                    'visible' => true,
                ],
                [
                    'label' => 'Nilai Saya',
                    'url' => route('mahasiswa.grades.index'),
                    'active' => request()->routeIs('mahasiswa.grades.*'),
                    'visible' => Gate::allows('viewMine', Grade::class),
                ],
                [
                    'label' => 'Pengguna',
                    'url' => route('users.index'),
                    'active' => request()->routeIs('users.*'),
                    'visible' => Gate::allows('viewAny', User::class),
                ],
                [
                    'label' => 'Tentang Kelompok',
                    'url' => route('tentang'),
                    'active' => request()->routeIs('tentang'),
                    'visible' => true,
                ],
            ], fn (array $item) => $item['visible']));

            $view->with('navItems', $navItems);
        });
    }
}