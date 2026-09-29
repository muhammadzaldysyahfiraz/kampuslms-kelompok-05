<?php // Menandai bahwa file menggunakan PHP.

namespace App\Http\Middleware; // Menentukan namespace middleware.

use Closure; // Mengimpor Closure untuk meneruskan request ke proses berikutnya.
use Illuminate\Http\Request; // Mengimpor Request untuk membaca user yang sedang login.
use Symfony\Component\HttpFoundation\Response; // Menentukan tipe response yang dikembalikan middleware.

class EnsureUserHasRole // Membuat middleware untuk memeriksa role pengguna.
{
    public function handle(Request $request, Closure $next, string ...$roles): Response // Menerima request, proses berikutnya, dan satu atau beberapa role.
    {
        $user = $request->user(); // Mengambil user yang sedang login dari request.

        if (!$user) { // Mengecek apakah belum ada user yang login.
            abort(401); // Menghentikan request dengan status 401 karena pengguna belum login.
        }

        if (!in_array($user->role, $roles, true)) { // Mengecek apakah role user tidak termasuk role yang diizinkan.
            abort(403); // Menghentikan request dengan status 403 karena user login tetapi role-nya tidak sesuai.
        }

        return $next($request); // Jika role sesuai, request diteruskan ke tujuan berikutnya.
    }
}