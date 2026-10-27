<x-layout title="Notifikasi">
    <div class="mx-auto max-w-4xl space-y-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-slate-500">Pusat informasi</p>
                <h1 class="mt-1 text-xl font-bold text-slate-900">Notifikasi</h1>
                <p class="mt-1 text-sm text-slate-600">{{ $unreadCount }} belum dibaca dari {{ $totalCount }} notifikasi.</p>
            </div>
            @if ($unreadCount > 0)
                <form action="{{ route('notifications.markAllRead') }}" method="POST">
                    @csrf
                    <button class="btn-secondary text-xs" type="submit">Tandai semua dibaca</button>
                </form>
            @endif
        </div>

        <div class="flex gap-2 border-b border-slate-200">
            <a href="{{ route('notifications.index', ['filter' => 'all']) }}" class="border-b-2 px-3 py-2 text-xs font-semibold {{ $filter === 'all' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500' }}">Semua</a>
            <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" class="border-b-2 px-3 py-2 text-xs font-semibold {{ $filter === 'unread' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500' }}">Belum dibaca</a>
        </div>

        <div class="divide-y divide-slate-200">
            @forelse ($notifications as $notification)
                @php
                    $data = $notification->data ?? [];
                    $title = $data['title'] ?? $data['assignment_title'] ?? 'Ada kabar baru';
                    $description = $data['course_name'] ?? 'KampusLMS';
                @endphp
                <article class="flex flex-col gap-3 py-4 sm:flex-row sm:items-center sm:justify-between {{ $notification->read_at ? '' : 'bg-slate-50 px-3' }}">
                    <a href="{{ route('notifications.show', $notification) }}" class="min-w-0 flex-1">
                        <div class="flex items-start gap-3">
                            <span class="mt-1 h-2 w-2 flex-none rounded-full {{ $notification->read_at ? 'bg-slate-300' : 'bg-rose-600' }}" aria-hidden="true"></span>
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-semibold text-slate-900">{{ $title }}</span>
                                <span class="mt-1 block text-xs text-slate-600">{{ $description }}</span>
                                <time class="mt-1 block text-[11px] text-slate-500" datetime="{{ $notification->created_at->toIso8601String() }}">{{ $notification->created_at->diffForHumans() }}</time>
                            </span>
                        </div>
                    </a>
                    <div class="flex items-center gap-3 pl-5 sm:pl-0">
                        @if (! $notification->read_at)
                            <form action="{{ route('notifications.read', $notification) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-xs font-semibold text-slate-600 underline decoration-slate-300 underline-offset-4 hover:text-slate-950">Tandai dibaca</button>
                            </form>
                        @endif
                        <form action="{{ route('notifications.destroy', $notification) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-semibold text-rose-700 hover:text-rose-900">Hapus</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="py-14 text-center">
                    <p class="text-sm font-semibold text-slate-800">Tidak ada notifikasi di sini.</p>
                    <p class="mt-1 text-xs text-slate-500">Notifikasi tugas dan nilai akan muncul di halaman ini.</p>
                </div>
            @endforelse
        </div>

        <div>{{ $notifications->links() }}</div>
    </div>
</x-layout>