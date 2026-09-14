<x-layout title="Detail: {{ $course->name }}">

    {{-- Navigasi Balik & Action Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <a href="{{ route('courses.index') }}" class="btn-outline text-xs py-2 w-fit">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            <span>Kembali ke Daftar</span>
        </a>

        <div class="flex items-center gap-2">
            <a href="{{ route('courses.edit', $course) }}" class="btn-secondary text-xs py-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                </svg>
                <span>Edit Mata Kuliah</span>
            </a>

            <form action="{{ route('courses.destroy', $course) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus mata kuliah {{ $course->name }}?')" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger-soft text-xs py-2 px-3">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                    <span>Hapus</span>
                </button>
            </form>
        </div>
    </div>

    {{-- Hero Banner Detail Mata Kuliah --}}
    <div class="lms-card p-6 sm:p-8 mb-8 relative overflow-hidden bg-gradient-to-r from-white via-slate-50 to-amber-50/20 border-l-4 border-l-[#FFC152]">
        <div class="flex flex-wrap items-center gap-2.5 mb-3">
            <span class="font-mono text-xs font-bold px-3 py-1 rounded-lg bg-slate-900 text-white">
                {{ $course->code }}
            </span>
            <span class="px-3 py-1 rounded-lg bg-[#FFC152]/20 text-[#925400] text-xs font-bold border border-[#FFC152]/40">
                {{ $course->sks }} SKS
            </span>

            @if ($course->status === 'active')
                <span class="badge-mint">Mata Kuliah Aktif</span>
            @elseif ($course->status === 'draft')
                <span class="badge-amber">Status Draft</span>
            @else
                <span class="badge-dark">Diarsipkan</span>
            @endif
        </div>

        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mb-3">
            {{ $course->name }}
        </h1>

        <p class="text-slate-600 text-sm max-w-3xl leading-relaxed">
            {{ $course->description ?? 'Tidak ada deskripsi rinci untuk mata kuliah ini. Pengajar dapat memperbarui silabus dan deskripsi melalui menu edit.' }}
        </p>
    </div>

    {{-- 2-Column Content Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        {{-- Sisi Kiri: Materi & Tugas (2 Kolom) --}}
        <div class="lg:col-span-2 space-y-8">
            
            {{-- Modul Materi Perkuliahan --}}
            <div class="lms-card p-6">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-sky-50 text-[#66A7F2] flex items-center justify-center font-bold">
                            📖
                        </div>
                        <div>
                            <h2 class="text-base font-extrabold text-slate-900">Materi Pembelajaran</h2>
                            <p class="text-xs text-slate-500">Bahan ajar dan referensi materi perkuliahan</p>
                        </div>
                    </div>
                    <span class="badge-sky text-[10px]">
                        {{ $course->materials->count() }} Materi
                    </span>
                </div>

                @if ($course->materials->count() > 0)
                    <div class="divide-y divide-slate-100">
                        @foreach ($course->materials as $material)
                            <div class="py-3.5 flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="text-sm font-bold text-slate-800 hover:text-[#66A7F2] transition-colors">
                                        {{ $material->title }}
                                    </h3>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        {{ $material->description ?? 'Tidak ada keterangan materi.' }}
                                    </p>
                                    @if ($material->external_url)
                                        <a href="{{ $material->external_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-xs font-semibold text-[#66A7F2] mt-1.5 hover:underline">
                                            <span>Buka Tautan Materi</span>
                                            <span>↗</span>
                                        </a>
                                    @endif
                                </div>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-600 uppercase">
                                    {{ $material->type }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 text-center text-slate-400 text-xs">
                        Belum ada materi yang diunggah untuk mata kuliah ini.
                    </div>
                @endif
            </div>

            {{-- Modul Tugas Perkuliahan --}}
            <div class="lms-card p-6">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 text-[#FFC152] flex items-center justify-center font-bold">
                            📝
                        </div>
                        <div>
                            <h2 class="text-base font-extrabold text-slate-900">Daftar Tugas & Evaluasi</h2>
                            <p class="text-xs text-slate-500">Penugasan dan tenggat waktu pengumpulan tugas</p>
                        </div>
                    </div>
                    <span class="badge-amber text-[10px]">
                        {{ $course->assignments->count() }} Tugas
                    </span>
                </div>

                @if ($course->assignments->count() > 0)
                    <div class="divide-y divide-slate-100">
                        @foreach ($course->assignments as $assignment)
                            <div class="py-3.5 flex items-start justify-between gap-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-sm font-bold text-slate-800">
                                            {{ $assignment->title }}
                                        </h3>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded {{ $assignment->status === 'published' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                            {{ ucfirst($assignment->status) }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-500 mt-1">
                                        Tenggat: <strong class="text-slate-700">{{ $assignment->due_at->format('d M Y, H:i') }}</strong>
                                        &bull; Skor Maksimal: {{ $assignment->max_score }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 text-center text-slate-400 text-xs">
                        Belum ada tugas yang dibuat untuk mata kuliah ini.
                    </div>
                @endif
            </div>

        </div>

        {{-- Sisi Kanan: Dosen Pengampu & Statistik (1 Kolom) --}}
        <div class="space-y-6">
            
            {{-- Kartu Dosen Pengampu --}}
            <div class="lms-card p-6">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">
                    Dosen Pengampu
                </h3>

                @if ($course->lecturer)
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-[#66A7F2] to-[#5DD299] flex items-center justify-center text-white font-extrabold text-base shadow-sm">
                            {{ strtoupper(substr($course->lecturer->name, 0, 2)) }}
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-sm font-extrabold text-slate-900 truncate">
                                {{ $course->lecturer->name }}
                            </h4>
                            <p class="text-xs text-slate-500 truncate">
                                {{ $course->lecturer->email }}
                            </p>
                        </div>
                    </div>

                    <div class="space-y-2 pt-3 border-t border-slate-100 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-500">NIP / ID:</span>
                            <span class="font-mono font-bold text-slate-800">{{ $course->lecturer->nim_nip ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Role:</span>
                            <span class="font-semibold text-slate-800 capitalize">{{ $course->lecturer->role }}</span>
                        </div>
                    </div>
                @else
                    <p class="text-xs text-rose-500 font-semibold">Dosen belum ditetapkan untuk mata kuliah ini.</p>
                @endif
            </div>

            {{-- Kartu Peserta Perkuliahan --}}
            <div class="lms-card p-6">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">
                    Peserta Perkuliahan
                </h3>

                <div class="flex items-baseline gap-2 mb-2">
                    <span class="text-3xl font-extrabold text-slate-900">
                        {{ $course->students_count ?? 0 }}
                    </span>
                    <span class="text-xs font-bold text-slate-500">Mahasiswa Terdaftar</span>
                </div>

                <div class="progress-bar my-3">
                    <div class="progress-bar-fill" style="width: {{ min(100, (($course->students_count ?? 0) / 30) * 100) }}%"></div>
                </div>

                <p class="text-[11px] text-slate-500">
                    Kapasitas kelas optimal sesuai seeder Milestone M1 (≥ 15 mahasiswa per mata kuliah).
                </p>
            </div>

            {{-- Kartu Informasi Sistem --}}
            <div class="lms-card p-6 text-xs text-slate-500 space-y-2">
                <div class="flex justify-between">
                    <span>Dibuat pada:</span>
                    <span class="font-semibold text-slate-700">{{ $course->created_at->format('d/m/Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Terakhir diubah:</span>
                    <span class="font-semibold text-slate-700">{{ $course->updated_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>

        </div>

    </div>

</x-layout>