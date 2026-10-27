<x-layout title="Pusat Pemberitahuan">

    {{-- Header Halaman --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 font-mono mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-900 transition-colors">Beranda</a>
                <span>/</span>
                <span class="text-slate-900 font-bold">Pemberitahuan</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2.5">
                <span>Pusat Pemberitahuan</span>
                @if ($unreadCount > 0)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#FE774C]/15 text-[#FE774C] font-mono border border-[#FE774C]/25">
                        {{ $unreadCount }} baru
                    </span>
                @endif
            </h1>
            <p class="text-xs text-slate-600 mt-0.5">
                Pantau penerbitan tugas baru, penilaian submission, dan pembaruan materi kelas secara asinkron.
            </p>
        </div>

        @if ($unreadCount > 0)
            <form action="{{ route('notifications.markAllRead') }}" method="POST" class="flex-shrink-0">
                @csrf
                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-xs font-semibold text-slate-700 shadow-2xs transition-colors cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                    <span>Tandai Semua Sudah Dibaca</span>
                </button>
            </form>
        @endif
    </div>

    {{-- Filter Bar & Tab Status --}}
    <div class="lms-card p-3 mb-6 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <div class="flex items-center gap-1.5" role="tablist" aria-label="Filter status notifikasi">
            <a href="{{ route('notifications.index', ['filter' => 'all']) }}" 
               class="px-3 py-1.5 rounded-md text-xs font-semibold transition-all {{ ($filter ?? 'all') === 'all' ? 'bg-[#111111] text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Semua Pemberitahuan ({{ $totalCount }})
            </a>
            <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" 
               class="px-3 py-1.5 rounded-md text-xs font-semibold transition-all {{ ($filter ?? '') === 'unread' ? 'bg-[#111111] text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Belum Dibaca ({{ $unreadCount }})
            </a>
        </div>

        <div class="text-[11px] text-slate-500 font-mono flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-[#10B981] inline-block animate-pulse"></span>
            <span>Sinkronisasi otomatis (polling aktif)</span>
        </div>
    </div>

    {{-- Daftar Kartu Notifikasi --}}
    @if ($notifications->isEmpty())
        <div class="lms-card p-12 text-center">
            <div class="w-12 h-12 rounded-2xl bg-slate-100 border border-slate-200 text-slate-400 flex items-center justify-center mx-auto mb-3.5">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                </svg>
            </div>
            <h3 class="text-sm font-bold text-slate-900">
                {{ ($filter ?? '') === 'unread' ? 'Tidak ada pemberitahuan yang belum dibaca' : 'Belum ada pemberitahuan saat ini' }}
            </h3>
            <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">
                Semua notifikasi pengumuman perkuliahan, penugasan tugas baru, dan skor nilai akan otomatis muncul di sini.
            </p>
            @if (($filter ?? '') === 'unread' && $totalCount > 0)
                <a href="{{ route('notifications.index', ['filter' => 'all']) }}" class="mt-4 inline-flex items-center text-xs font-semibold text-slate-700 hover:text-slate-950 underline">
                    Lihat riwayat notifikasi sebelumnya &rarr;
                </a>
            @endif
        </div>
    @else
        <div class="space-y-2.5">
            @foreach ($notifications as $notification)
                @php
                    $isUnread = is_null($notification->read_at);
                    $data = $notification->data ?? [];
                    $title = $data['title'] ?? 'Pemberitahuan Sistem';
                    $courseName = $data['course_name'] ?? null;
                    $message = $data['message'] ?? ($data['instructions'] ?? 'Terdapat pembaruan data perkuliahan pada akun Anda.');
                    $url = $data['url'] ?? route('notifications.show', $notification);
                    $type = class_basename($notification->type);
                @endphp

                <div class="lms-card p-4 transition-all hover:border-slate-300 {{ $isUnread ? 'bg-[#FFFDFB] border-l-4 border-l-[#FE774C]' : 'bg-white' }}">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                        <div class="flex items-start gap-3.5">
                            {{-- Ikon Tipe Notifikasi --}}
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 {{ $isUnread ? 'bg-[#FE774C]/10 text-[#FE774C]' : 'bg-slate-100 text-slate-500' }}">
                                @if (str_contains($type, 'Assignment'))
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                @elseif (str_contains($type, 'Grade'))
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342" />
                                    </svg>
                                @else
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                                    </svg>
                                @endif
                            </div>

                            {{-- Isi Notifikasi --}}
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h4 class="text-xs font-bold text-slate-900 hover:text-slate-950 transition-colors">
                                        {{ $title }}
                                    </h4>
                                    @if ($isUnread)
                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-[#FE774C]/10 text-[#FE774C] font-mono">
                                            Baru
                                        </span>
                                    @endif
                                    @if ($courseName)
                                        <span class="text-[11px] text-slate-500 font-medium">
                                            &bull; {{ $courseName }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                                    {{ $message }}
                                </p>
                                <div class="flex items-center gap-3 mt-2 text-[11px] text-slate-400 font-mono">
                                    <span>{{ $notification->created_at->diffForHumans() }}</span>
                                    <span>&bull;</span>
                                    <span>{{ $notification->created_at->format('d M Y, H:i') }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Tombol Aksi --}}
                        <div class="flex items-center gap-1.5 self-end sm:self-center flex-shrink-0">
                            @if (!empty($url))
                                <a href="{{ route('notifications.show', $notification) }}" 
                                   class="px-2.5 py-1.5 rounded-lg bg-[#111111] hover:bg-slate-800 text-white text-[11px] font-semibold transition-colors flex items-center gap-1">
                                    <span>Lihat</span>
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                    </svg>
                                </a>
                            @endif

                            @if ($isUnread)
                                <form action="{{ route('notifications.read', $notification) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" title="Tandai sudah dibaca" class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:text-slate-950 hover:bg-slate-100 transition-colors cursor-pointer" aria-label="Tandai dibaca">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                        </svg>
                                    </button>
                                </form>
                            @endif

                            <form action="{{ route('notifications.destroy', $notification) }}" method="POST" class="inline" onsubmit="return confirm('Hapus notifikasi ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Hapus notifikasi" class="p-1.5 rounded-lg border border-slate-200 text-slate-400 hover:text-rose-600 hover:border-rose-200 hover:bg-rose-50 transition-colors cursor-pointer" aria-label="Hapus notifikasi">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
    @endif

</x-layout>
