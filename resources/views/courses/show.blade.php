<x-layout title="Detail: {{ $course->name }}">

    {{-- Navigasi Balik & Action Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <a href="{{ route('courses.index') }}" class="btn-outline text-xs py-2 w-fit inline-flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-600" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            <span>Kembali ke Daftar</span>
        </a>

        @if ($activeRole !== 'mahasiswa')
            <div class="flex items-center gap-2">
                <a href="{{ route('courses.edit', $course) }}" class="btn-secondary text-xs py-2">
                    <svg class="w-4 h-4 text-slate-700" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                    </svg>
                    <span>Edit Mata Kuliah</span>
                </a>

                <form action="{{ route('courses.destroy', $course) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus mata kuliah {{ $course->name }}?')" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger-soft text-xs py-2 px-3" aria-label="Hapus mata kuliah {{ $course->name }}">
                        <svg class="w-4 h-4 text-rose-700" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                        <span>Hapus</span>
                    </button>
                </form>
            </div>
        @else
            <div class="flex items-center gap-2">
                <span class="badge-mint text-xs py-1.5 px-3 font-semibold">🎓 Mahasiswa Terdaftar</span>
            </div>
        @endif
    </div>

    {{-- Card Header Detail Mata Kuliah --}}
    <div class="lms-card p-6 sm:p-7 mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-3">
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">
                {{ $course->name }}
            </h1>

            <div class="flex items-center gap-2">
                <span class="font-mono text-xs font-bold px-2.5 py-1 rounded-md bg-slate-100 text-slate-800 border border-slate-200">
                    {{ $course->code }}
                </span>
                <span class="text-xs font-bold px-2.5 py-1 rounded-md bg-slate-100 text-slate-800 border border-slate-200 font-mono">
                    {{ $course->sks }} SKS
                </span>
                @if ($course->status === 'active')
                    <span class="badge-mint text-[11px]">Aktif</span>
                @elseif ($course->status === 'draft')
                    <span class="badge-amber text-[11px]">Draft</span>
                @else
                    <span class="badge-dark text-[11px]">Diarsipkan</span>
                @endif
            </div>
        </div>

        <p class="text-slate-700 text-sm max-w-3xl leading-relaxed font-normal">
            {{ $course->description ?? 'Tidak ada deskripsi rinci untuk mata kuliah ini. Pengajar dapat memperbarui silabus dan deskripsi melalui menu edit.' }}
        </p>
    </div>

    {{-- 2-Column Content Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        
        {{-- Sisi Kiri: Materi & Tugas (2 Kolom) --}}
        <div class="lg:col-span-2 space-y-7">
            
            {{-- Modul Materi Perkuliahan --}}
            <div class="lms-card p-6">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-800 flex items-center justify-center font-bold" aria-hidden="true">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Materi Pembelajaran</h2>
                            <p class="text-xs text-slate-600 font-normal">Bahan ajar dan referensi materi perkuliahan</p>
                        </div>
                    </div>
                    <span class="badge-sky text-[11px]">
                        {{ $course->materials->count() }} Materi
                    </span>
                </div>

                @if ($course->materials->count() > 0)
                    <div class="divide-y divide-slate-100">
                        @foreach ($course->materials as $material)
                            <div class="py-3.5 flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="text-sm font-semibold text-slate-900 hover:text-slate-950 transition-colors">
                                        {{ $material->title }}
                                    </h3>
                                    <p class="text-xs text-slate-600 mt-0.5 font-normal leading-relaxed">
                                        {{ $material->description ?? 'Tidak ada keterangan materi.' }}
                                    </p>
                                    @if ($material->external_url)
                                        <a href="{{ $material->external_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-xs font-semibold text-sky-800 mt-1.5 hover:underline focus:outline-none focus:underline">
                                            <span>Buka Tautan Materi</span>
                                            <span class="sr-only">(buka di tab baru)</span>
                                            <svg class="w-3.5 h-3.5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 19.5 15-15m0 0H8.25m11.25 0v11.25" />
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                                <span class="text-[11px] font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-700 uppercase font-mono border border-slate-200">
                                    {{ $material->type }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 text-center text-slate-600 text-xs font-normal">
                        Belum ada materi yang diunggah untuk mata kuliah ini.
                    </div>
                @endif
            </div>

            {{-- Modul Tugas Perkuliahan --}}
            <div class="lms-card p-6">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-800 flex items-center justify-center font-bold" aria-hidden="true">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Daftar Tugas & Evaluasi</h2>
                            <p class="text-xs text-slate-600 font-normal">Penugasan dan tenggat waktu pengumpulan tugas</p>
                        </div>
                    </div>
                    <span class="badge-amber text-[11px]">
                        {{ $course->assignments->count() }} Tugas
                    </span>
                </div>

                @if ($course->assignments->count() > 0)
                    <div class="divide-y divide-slate-100">
                        @foreach ($course->assignments as $assignment)
                            <div class="py-3.5 flex items-start justify-between gap-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-sm font-semibold text-slate-900">
                                            {{ $assignment->title }}
                                        </h3>
                                        <span class="text-[11px] font-bold px-2 py-0.5 rounded {{ $assignment->status === 'published' ? 'badge-mint' : 'badge-dark' }}">
                                            {{ ucfirst($assignment->status) }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-600 mt-1 font-normal">
                                        Tenggat: <strong class="text-slate-800 font-mono">{{ $assignment->due_at->format('d M Y, H:i') }}</strong>
                                        &bull; Skor Maksimal: <span class="font-mono font-bold text-slate-900">{{ $assignment->max_score }} pts</span>
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 text-center text-slate-600 text-xs font-normal">
                        Belum ada tugas yang dibuat untuk mata kuliah ini.
                    </div>
                @endif
            </div>

        </div>

        {{-- Sisi Kanan: Single Cohesive Module (Dosen, Peserta, Info Sistem) --}}
        <div class="w-full space-y-6">
            
            <div class="lms-card p-6">
                {{-- Dosen Pengampu --}}
                <div class="pb-5 border-b border-slate-100">
                    <h3 class="text-xs font-bold text-slate-600 uppercase tracking-wider mb-3.5">
                        Dosen Pengampu
                    </h3>

                    @if ($course->lecturer)
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-slate-100 border border-slate-200 text-slate-900 flex items-center justify-center font-bold text-xs flex-shrink-0" aria-hidden="true">
                                {{ strtoupper(substr($course->lecturer->name, 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-sm font-bold text-slate-900 truncate">
                                    {{ $course->lecturer->name }}
                                </h4>
                                <p class="text-xs text-slate-600 truncate font-mono">
                                    {{ $course->lecturer->email }}
                                </p>
                            </div>
                        </div>

                        <div class="flex justify-between text-xs font-normal pt-3 text-slate-700">
                            <span class="text-slate-600">NIP / ID:</span>
                            <span class="font-mono font-semibold text-slate-900">{{ $course->lecturer->nim_nip ?? '-' }}</span>
                        </div>
                    @else
                        <p class="text-xs text-rose-700 font-medium">Dosen belum ditetapkan untuk mata kuliah ini.</p>
                    @endif
                </div>

                {{-- Peserta Perkuliahan --}}
                <div class="py-5 border-b border-slate-100">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">Peserta Kuliah</span>
                        <span class="text-xs font-bold text-slate-900 font-mono">
                            {{ $course->students_count ?? ($course->students ? $course->students->count() : 0) }} / 30
                        </span>
                    </div>

                    @php
                        $stdCount = $course->students_count ?? ($course->students ? $course->students->count() : 0);
                        $enrollPct = min(100, max(15, ($stdCount / 30) * 100));
                    @endphp

                    <div 
                        class="w-full h-2 bg-slate-100 rounded-full overflow-hidden mb-2"
                        role="progressbar"
                        aria-valuenow="{{ $stdCount }}"
                        aria-valuemin="0"
                        aria-valuemax="30"
                        aria-label="Kapasitas mahasiswa terdaftar pada mata kuliah ini"
                    >
                        <div class="h-full bg-slate-900 rounded-full transition-all duration-500" style="width: {{ $enrollPct }}%"></div>
                    </div>

                    <p class="text-[11px] text-slate-600 font-normal">
                        Kapasitas kelas reguler aktif (maksimal 30 mahasiswa).
                    </p>
                </div>

                {{-- Metadata Sistem --}}
                <div class="pt-4 text-xs text-slate-600 space-y-1.5 font-normal">
                    <div class="flex justify-between">
                        <span>Dibuat:</span>
                        <span class="font-mono font-semibold text-slate-800">{{ $course->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Pembaruan:</span>
                        <span class="font-mono font-semibold text-slate-800">{{ $course->updated_at->format('d M Y, H:i') }}</span>
                    </div>
                </div>
            </div>

        </div>

    </div>

</x-layout>