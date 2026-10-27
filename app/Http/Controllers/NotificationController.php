<?php

namespace App\Http\Controllers;

use App\Notifications\GradeReleasedNotification;
use App\Notifications\NewAssignmentNotification;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request): mixed
    {
        $user = $request->user();
        abort_unless($user, 401);

        $filter = $request->query('filter', 'all') === 'unread' ? 'unread' : 'all';
        $query = $user->notifications()->latest();
        if ($filter === 'unread') {
            $query->whereNull('read_at');
        }

        $notifications = $query->paginate(15)->withQueryString();
        $unreadCount = $user->unreadNotifications()->count();
        $totalCount = $user->notifications()->count();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'notifications' => $notifications,
                'unread_count' => $unreadCount,
                'total_count' => $totalCount,
            ]);
        }

        return view('notifications.index', compact(
            'notifications',
            'unreadCount',
            'totalCount',
            'filter'
        ));
    }

    public function show(Request $request, DatabaseNotification $notification): mixed
    {
        $this->ensureOwner($request, $notification);
        $notification->markAsRead();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['notification' => $notification]);
        }

        $destination = match ($notification->type) {
            NewAssignmentNotification::class => route('assignments.show', $notification->data['assignment_id']),
            GradeReleasedNotification::class => route('mahasiswa.grades.index'),
            default => route('notifications.index'),
        };

        return redirect($destination);
    }

    public function read(Request $request, DatabaseNotification $notification): mixed
    {
        $this->ensureOwner($request, $notification);
        $notification->markAsRead();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Notifikasi ditandai telah dibaca.']);
        }

        return back()->with('success', 'Notifikasi ditandai telah dibaca.');
    }

    public function markAllRead(Request $request): mixed
    {
        $user = $request->user();
        abort_unless($user, 401);
        $user->unreadNotifications()->update(['read_at' => now()]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Semua notifikasi ditandai telah dibaca.']);
        }

        return back()->with('success', 'Semua notifikasi ditandai telah dibaca.');
    }

    public function destroy(Request $request, DatabaseNotification $notification): mixed
    {
        $this->ensureOwner($request, $notification);
        $notification->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Notifikasi berhasil dihapus.']);
        }

        return back()->with('success', 'Notifikasi berhasil dihapus.');
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
