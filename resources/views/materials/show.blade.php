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

        @can('update', $material->course)
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

                <form action="{{ route('materials.destroy', $material) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus materi ini?')" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger-soft text-xs py-2 px-3 inline-flex items-center gap-1.5" aria-label="Hapus materi {{ $material->title }}">
                        <svg class="w-4 h-4 text-rose-700" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                        <span>Hapus</span>
                    </button>
                </form>
            </div>
        @endcan
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

        {{-- Kolom Utama: Konten Materi --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="lms-card p-6 sm:p-7">
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-[11px] font-bold px-2.5 py-1 rounded-md bg-slate-100 text-slate-800 border border-slate-200 uppercase font-mono">
                        Tipe: {{ $material->type }}
                    </span>
                    <span class="text-xs text-slate-500 font-mono">
                        {{ $material->created_at->format('d M Y, H:i') }}
                    </span>
                </div>

                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight leading-snug mb-3">
                    {{ $material->title }}
                </h1>

                <p class="text-xs text-slate-600 mb-6 font-normal">
                    Mata kuliah: <a href="{{ route('courses.show', $material->course) }}" class="font-semibold text-slate-800 hover:underline">{{ $material->course->name }}</a>
                </p>

                <div class="border-t border-slate-100 pt-5 space-y-4">
                    <h2 class="text-xs font-bold text-slate-600 uppercase tracking-wider">
                        Deskripsi & Rincian Pembahasan
                    </h2>
                    <div class="rounded-lg border border-slate-200 bg-slate-50/70 p-4 text-sm text-slate-800 leading-relaxed whitespace-pre-line">
                        {{ $material->description ?: 'Tidak ada deskripsi materi.' }}
                    </div>

                    @if ($material->external_url)
                        <div class="pt-2">
                            <a
                                href="{{ $material->external_url }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="btn-primary text-xs inline-flex items-center gap-2"
                            >
                                <span>Buka Tautan Materi Luar</span>
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
                <h3 class="text-xs font-bold text-slate-600 uppercase tracking-wider mb-4">
                    Informasi Bahan Ajar
                </h3>

                <dl class="space-y-3 text-xs">
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-600 font-normal">Mata Kuliah</dt>
                        <dd class="text-slate-900 font-semibold text-right">{{ $material->course->code }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-600 font-normal">SKS</dt>
                        <dd class="text-slate-900 font-mono text-right">{{ $material->course->sks }} SKS</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-600 font-normal">Diunggah oleh</dt>
                        <dd class="text-slate-900 font-medium text-right">{{ $material->uploader->name ?? ($material->course->lecturer->name ?? 'Dosen') }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-600 font-normal">Waktu Terbit</dt>
                        <dd class="text-slate-900 font-mono text-right">{{ $material->created_at->format('d M Y') }}</dd>
                    </div>
                </dl>

                <div class="mt-5 pt-4 border-t border-slate-100">
                    <a href="{{ route('courses.show', $material->course) }}" class="btn-outline text-xs w-full text-center block">
                        Kembali ke Mata Kuliah
                    </a>
                </div>
            </div>
        </div>

    </div>

</x-layout>
