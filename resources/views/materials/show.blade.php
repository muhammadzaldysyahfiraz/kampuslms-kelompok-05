<x-layout title="{{ $material->title }} — {{ $material->course->name }}">

    {{-- Breadcrumb & Aksi --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-2 text-xs text-slate-600">
            <a href="{{ route('courses.index') }}" class="text-slate-500 hover:text-slate-800 transition-colors">Mata kuliah</a>
            <span class="text-slate-300" aria-hidden="true">/</span>
            <a href="{{ route('courses.show', $material->course) }}" class="text-slate-500 hover:text-slate-800 transition-colors">
                {{ $material->course->name }}
            </a>
            <span class="text-slate-300" aria-hidden="true">/</span>
            <span class="text-slate-800 font-semibold">{{ $material->title }}</span>
        </div>

        @can('update', $material)
            <div class="flex items-center gap-2">
                <a
                    href="{{ route('materials.edit', $material) }}"
                    class="btn-secondary text-xs inline-flex items-center gap-2"
                >
                    <svg class="w-4 h-4 text-slate-700" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                    </svg>
                    Edit Materi
                </a>

                <button
                    type="button"
                    onclick="document.getElementById('delete-material-dialog')?.showModal()"
                    class="btn-danger-soft text-xs py-2 px-3 inline-flex items-center gap-1.5"
                    aria-label="Hapus materi {{ $material->title }}"
                >
                    <svg class="w-4 h-4 text-rose-700" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                    <span>Hapus</span>
                </button>
            </div>
        @endcan
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

        {{-- Kolom Utama: Konten Materi --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="lms-card p-6 sm:p-7">
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-[11px] font-semibold px-2.5 py-1 rounded-md bg-slate-100 text-slate-700 font-mono">
                        {{ $material->type === 'file' ? 'Berkas Unduhan' : 'Tautan Eksternal' }}
                    </span>
                    <span class="text-xs text-slate-500 font-mono">
                        {{ $material->created_at->format('d M Y, H:i') }}
                    </span>
                </div>

                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight leading-snug mb-5">
                    {{ $material->title }}
                </h1>

                <div class="border-t border-slate-100 pt-5 space-y-4">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">
                        Deskripsi Materi
                    </h2>
                    <div class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">
                        {{ $material->description ?: 'Tidak ada deskripsi materi.' }}
                    </div>

                    @if ($material->type === 'file' && $material->file_path)
                        <div class="pt-5 border-t border-slate-100">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-lg bg-slate-100 text-slate-800 flex items-center justify-center shrink-0 border border-slate-200">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-slate-900 truncate">{{ $material->original_name ?: 'Berkas Materi' }}</p>
                                        <p class="text-xs text-slate-500 font-mono mt-0.5">
                                            {{ $material->file_size ? \Illuminate\Support\Number::fileSize($material->file_size) : '-' }}
                                            @if ($material->mime_type)
                                                · {{ $material->mime_type }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <a
                                    href="{{ route('materials.download', $material) }}"
                                    class="btn-primary text-xs inline-flex items-center justify-center gap-2 shrink-0 py-2.5 px-4"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                    </svg>
                                    <span>Unduh Berkas</span>
                                </a>
                            </div>
                        </div>
                    @endif

                    @if ($material->external_url)
                        <div class="pt-5 border-t border-slate-100">
                            <a
                                href="{{ $material->external_url }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="btn-primary text-xs inline-flex items-center gap-2"
                            >
                                <span>Buka Tautan Materi</span>
                                <svg class="w-4 h-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                </svg>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Kolom Samping: Info Perkuliahan & Pengunggah --}}
        <div class="space-y-6">
            <div class="lms-card p-6">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-4">
                    Informasi Bahan Ajar
                </h3>

                <dl class="space-y-3 text-xs">
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-500 font-normal">Mata Kuliah</dt>
                        <dd class="text-slate-900 font-semibold text-right">
                            <a href="{{ route('courses.show', $material->course) }}" class="hover:underline">
                                {{ $material->course->code }}
                            </a>
                        </dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-500 font-normal">SKS</dt>
                        <dd class="text-slate-900 font-mono text-right">{{ $material->course->sks }} SKS</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-500 font-normal">Pengunggah</dt>
                        <dd class="text-slate-900 font-medium text-right">{{ $material->uploader->name ?? ($material->course->lecturer->name ?? 'Dosen') }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-500 font-normal">Waktu Terbit</dt>
                        <dd class="text-slate-900 font-mono text-right">{{ $material->created_at->format('d M Y, H:i') }}</dd>
                    </div>
                </dl>
            </div>
        </div>

    </div>

    {{-- Modern Web Guidance: light-dismiss-a-dialog (<dialog closedby="any">) --}}
    @can('update', $material)
    <dialog
        id="delete-material-dialog"
        closedby="any"
        aria-labelledby="delete-dialog-title"
        class="rounded-xl border border-slate-200 bg-white p-6 shadow-2xl backdrop:bg-slate-900/50 backdrop:backdrop-blur-sm max-w-md w-full m-auto"
    >
        <div class="space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                </div>
                <div>
                    <h3 id="delete-dialog-title" class="text-base font-bold text-slate-900">Hapus Materi Perkuliahan?</h3>
                    <p class="text-xs text-slate-500">Tindakan ini permanen dan akan membersihkan berkas fisik.</p>
                </div>
            </div>

            <p class="text-xs text-slate-600 leading-relaxed">
                Apakah Anda yakin ingin menghapus materi <strong class="text-slate-800">"{{ $material->title }}"</strong>? Berkas dan riwayat materi tidak dapat dipulihkan kembali.
            </p>

            <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-slate-100">
                <button
                    type="button"
                    onclick="document.getElementById('delete-material-dialog')?.close()"
                    class="btn-outline text-xs py-2 px-3.5"
                >
                    Batal
                </button>
                <form action="{{ route('materials.destroy', $material) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger-soft text-xs py-2 px-3.5">
                        Ya, Hapus Sekarang
                    </button>
                </form>
            </div>
        </div>
    </dialog>

    <script>
        // Fallback light-dismiss untuk peramban yang belum mendukung atribut closedby
        const deleteDialog = document.getElementById('delete-material-dialog');
        if (deleteDialog && !('closedBy' in HTMLDialogElement.prototype)) {
            deleteDialog.addEventListener('click', (event) => {
                if (event.target !== deleteDialog) return;
                const rect = deleteDialog.getBoundingClientRect();
                const isInDialog = (
                    rect.top <= event.clientY && event.clientY <= rect.top + rect.height &&
                    rect.left <= event.clientX && event.clientX <= rect.left + rect.width
                );
                if (!isInDialog) deleteDialog.close();
            });
        }
    </script>
    @endcan

</x-layout>
