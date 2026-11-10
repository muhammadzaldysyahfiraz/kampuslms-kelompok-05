<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
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
    }
}