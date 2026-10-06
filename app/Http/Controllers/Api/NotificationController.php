<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        // Hanya notifikasi milik pengguna yang login, diambil lewat relasinya.
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(15);

        return NotificationResource::collection($notifications);
    }

    public function read(Request $request, string $id)
    {
        // Dicari lewat relasi pengguna, bukan dari seluruh tabel.
        // Notifikasi milik orang lain tidak akan ketemu, jadi hasilnya 404.
        $notification = $request->user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        return new NotificationResource($notification->fresh());
    }
}