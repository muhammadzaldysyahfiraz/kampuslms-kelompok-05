<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user, 401);

        return response()->json([
            'notifications' => $user->notifications()->latest()->paginate(20),
        ]);
    }

    public function show(Request $request, DatabaseNotification $notification): JsonResponse
    {
        $this->ensureOwner($request, $notification);
        return response()->json(['notification' => $notification]);
    }

    public function read(Request $request, DatabaseNotification $notification): JsonResponse
    {
        $this->ensureOwner($request, $notification);
        $notification->markAsRead();
        return response()->json(['message' => 'Notifikasi ditandai telah dibaca.']);
    }

    public function destroy(Request $request, DatabaseNotification $notification): JsonResponse
    {
        $this->ensureOwner($request, $notification);
        $notification->delete();
        return response()->json(['message' => 'Notifikasi berhasil dihapus.']);
    }

    private function ensureOwner(Request $request, DatabaseNotification $notification): void
    {
        $user = $request->user();
        abort_unless($user, 401);
        // 404 menyamarkan keberadaan notifikasi milik pengguna lain.
        abort_unless(
            $user->notifications()->whereKey($notification->getKey())->exists(),
            404
        );
    }
}
