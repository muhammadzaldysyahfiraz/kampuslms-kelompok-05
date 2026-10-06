<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(
        Request $request,
        Closure $next,
        string ...$roles
    ): Response {
        // Mengambil pengguna yang sedang login.
        $user = $request->user();

        // Tolak jika pengguna belum login.
        if (!$user) {
            abort(401);
        }

        // Tolak jika role pengguna tidak diizinkan.
        if (!in_array($user->role, $roles, true)) {
            abort(403);
        }

        // Lanjutkan request jika role sesuai.
        return $next($request);
    }
}