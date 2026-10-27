<x-layout title="Rekap Nilai Saya">

    {{-- Navigasi Balik & Action Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <a href="{{ route('dashboard') }}" class="btn-outline text-xs py-2 w-fit inline-flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-600" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            <span>Kembali ke Dashboard</span>
        </a>

        <div class="flex items-center gap-2 text-xs text-slate-500 font-mono">
            <span>Peran:</span>
            <span class="status status-mahasiswa font-bold">
                <span class="status-dot" aria-hidden="true"></span>Mahasiswa
            </span>
        </div>
    </div>

    {{-- Card Header: Rekap Nilai Saya --}}
    <div class="lms-card p-6 sm:p-7 mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 font-mono">Transkrip Akademik</span>
                    <span class="text-slate-300">·</span>
                    <span class="text-xs text-slate-500 font-mono">{{ now()->format('Y') }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">
                    Rekap Nilai Saya
                </h1>
                <p class="text-slate-600 text-xs sm:text-sm mt-1 max-w-2xl leading-relaxed">
                    Ringkasan evaluasi penugasan, transparansi penilaian dosen, dan akumulasi nilai mata kuliah Anda.
                </p>
            </div>

            <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-200/80 self-start sm:self-auto">
                <div class="w-10 h-10 rounded-lg bg-slate-900 text-white flex items-center justify-center font-bold text-xs font-mono">
                    {{ strtoupper(substr(auth()->user()->name ?? 'M', 0, 2)) }}
                </div>
                <div class="text-xs">
                    <p class="font-bold text-slate-900 leading-tight">{{ auth()->user()->name }}</p>
                    <p class="text-slate-500 font-mono text-[11px] mt-0.5">{{ auth()->user()->nim_nip ?? auth()->user()->email }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Ringkasan Statistik (Bento Stat Cards) --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
        
        {{-- Stat 1: Rata-rata Nilai --}}
        <div class="two-tone-card">
            <div class="two-tone-body">
                <div class="flex items-center justify-between text-slate-500 mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider font-mono">Rata-rata Nilai</span>
                    <div class="w-7 h-7 rounded-md bg-[#EDF3EC] text-[#245228] flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                        </svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-slate-900 font-mono tabular-nums tracking-tight">
                        {{ $averageScore !== null ? number_format($averageScore, 1) : '-' }}
                    </span>
                    @if ($averageScore !== null)
                        <span class="text-xs font-mono font-semibold text-slate-400">/ 100</span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    {{ $averageScore !== null ? 'Skor kumulatif seluruh tugas dinilai' : 'Belum ada nilai tugas yang diterbitkan' }}
                </p>
            </div>
            <div class="two-tone-footer text-slate-600 font-mono text-[11px]">
                <span>Indeks Akademik</span>
                @if ($averageScore !== null)
                    <span class="font-bold text-slate-900">
                        @if ($averageScore >= 85) Predikat A
                        @elseif ($averageScore >= 70) Predikat B
                        @elseif ($averageScore >= 55) Predikat C
                        @else Predikat D
                        @endif
                    </span>
                @else
                    <span>Tidak Ada Data</span>
                @endif
            </div>
        </div>

        {{-- Stat 2: Total Tugas Terkumpul --}}
        <div class="two-tone-card">
            <div class="two-tone-body">
                <div class="flex items-center justify-between text-slate-500 mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider font-mono">Tugas Terkumpul</span>
                    <div class="w-7 h-7 rounded-md bg-sky-50 text-sky-700 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-slate-900 font-mono tabular-nums tracking-tight">
                        {{ $totalSubmitted }}
                    </span>
                    <span class="text-xs font-mono font-semibold text-slate-400">tugas</span>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    Total berkas penugasan yang berhasil Anda kirimkan
                </p>
            </div>
            <div class="two-tone-footer text-slate-600 font-mono text-[11px]">
                <span>Status Keaktifan</span>
                <span class="font-semibold text-sky-800">{{ $totalSubmitted > 0 ? 'Aktif Mengumpulkan' : 'Belum Ada Tugas' }}</span>
            </div>
        </div>

        {{-- Stat 3: Tugas Dinilai --}}
        <div class="two-tone-card">
            <div class="two-tone-body">
                <div class="flex items-center justify-between text-slate-500 mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider font-mono">Tugas Dinilai</span>
                    <div class="w-7 h-7 rounded-md bg-amber-50 text-amber-700 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                        </svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-slate-900 font-mono tabular-nums tracking-tight">
                        {{ $totalGraded }}
                    </span>
                    <span class="text-xs font-mono font-semibold text-slate-400">/ {{ $totalSubmitted }} selesai</span>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    {{ $totalSubmitted - $totalGraded > 0 ? ($totalSubmitted - $totalGraded) . ' tugas menunggu penilaian dosen' : 'Semua tugas terkumpul telah dinilai' }}
                </p>
            </div>
            <div class="two-tone-footer text-slate-600 font-mono text-[11px]">
                <span>Tingkat Evaluasi</span>
                <span class="font-semibold text-slate-900">
                    {{ $totalSubmitted > 0 ? round(($totalGraded / $totalSubmitted) * 100) . '%' : '0%' }}
                </span>
            </div>
        </div>

    </div>

    {{-- Daftar Nilai per Mata Kuliah --}}
    <div class="space-y-8">
        
        <div class="flex items-center justify-between pb-3 border-b border-slate-200">
            <div>
                <h2 class="text-lg font-bold text-slate-900 tracking-tight">
                    Rincian Nilai per Mata Kuliah
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Daftar penugasan, batas waktu, skor perolehan, dan umpan balik resmi dosen.
                </p>
            </div>
            <span class="text-xs font-mono font-bold px-2.5 py-1 rounded-md bg-slate-100 text-slate-700 border border-slate-200">
                {{ $courseGroups->count() }} Mata Kuliah
            </span>
        </div>

        @forelse ($courseGroups as $group)
            @php
                $course = $group->course;
                $courseSubs = $group->submissions;
            @endphp

            <div class="lms-card overflow-hidden">
                {{-- Header Kartu Mata Kuliah --}}
                <div class="p-5 sm:p-6 bg-slate-50/70 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-1.5">
                            <span class="font-mono text-xs font-bold px-2 py-0.5 rounded bg-white text-slate-800 border border-slate-200">
                                {{ $course->code }}
                            </span>
                            <span class="font-mono text-xs font-semibold px-2 py-0.5 rounded bg-white text-slate-600 border border-slate-200">
                                {{ $course->sks }} SKS
                            </span>
                            @if ($course->status === 'active')
                                <span class="status status-active text-xs"><span class="status-dot" aria-hidden="true"></span>Aktif</span>
                            @endif
                        </div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 truncate">
                            <a href="{{ route('courses.show', $course) }}" class="hover:text-sky-700 transition-colors">
                                {{ $course->name }}
                            </a>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Pengajar: <span class="font-semibold text-slate-700">{{ $course->lecturer->name ?? 'Dosen Pengampu' }}</span>
                            @if ($course->lecturer?->nim_nip)
                                <span class="font-mono text-slate-400">({{ $course->lecturer->nim_nip }})</span>
                            @endif
                        </p>
                    </div>

                    <div class="flex items-center gap-3 sm:gap-4 shrink-0">
                        <div class="text-right">
                            <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-slate-400 block">Rata-rata Kelas Ini</span>
                            @if ($group->average_score !== null)
                                <span class="tabular-nums font-mono text-xl font-bold text-slate-900">
                                    {{ number_format($group->average_score, 1) }}
                                </span>
                                <span class="text-xs text-slate-400 font-mono">/ 100</span>
                            @else
                                <span class="text-xs font-mono text-slate-400 italic">Belum Ada Nilai</span>
                            @endif
                        </div>
                        <a href="{{ route('courses.show', $course) }}" class="btn-outline text-xs py-1.5 px-3">
                            <span>Detail MK</span>
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- Daftar Penugasan pada Mata Kuliah --}}
                @if ($courseSubs->isNotEmpty())
                    <div class="divide-y divide-slate-100">
                        @foreach ($courseSubs as $sub)
                            @php
                                $assignment = $sub->assignment;
                                $grade = $sub->grade;
                            @endphp
                            <div class="p-5 sm:p-6 space-y-3.5">
                                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('submissions.show', $sub) }}" class="text-sm sm:text-base font-bold text-slate-900 hover:text-sky-700 transition-colors">
                                                {{ $assignment->title }}
                                            </a>
                                            @if ($sub->is_late)
                                                <span class="status status-late text-xs"><span class="status-dot" aria-hidden="true"></span>Terlambat</span>
                                            @else
                                                <span class="status status-ontime text-xs"><span class="status-dot" aria-hidden="true"></span>Tepat Waktu</span>
                                            @endif
                                        </div>

                                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1 text-xs text-slate-500 font-mono">
                                            <span>
                                                Batas Waktu: <strong class="text-slate-700 font-semibold">{{ $assignment->due_at?->format('d M Y, H:i') ?? '-' }}</strong>
                                            </span>
                                            <span class="text-slate-300">·</span>
                                            <span>
                                                Dikumpulkan: <strong class="text-slate-700 font-semibold">{{ $sub->submitted_at?->format('d M Y, H:i') ?? '-' }}</strong>
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Skor Nilai --}}
                                    <div class="text-left sm:text-right shrink-0">
                                        @if ($grade !== null)
                                            <div class="flex items-baseline sm:justify-end gap-1">
                                                <span class="tabular-nums font-mono text-2xl font-bold text-slate-900">
                                                    {{ number_format($grade->score, 0) }}
                                                </span>
                                                <span class="tabular-nums font-mono text-xs text-slate-400 font-semibold">
                                                    / {{ $assignment->max_score }}
                                                </span>
                                            </div>
                                            <span class="text-[10px] font-mono text-emerald-700 font-semibold block">
                                                Telah Dinilai
                                            </span>
                                        @else
                                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-amber-50 text-amber-800 border border-amber-200 text-xs font-semibold font-mono">
                                                <svg class="w-3.5 h-3.5 text-amber-600 animate-pulse" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                </svg>
                                                <span>Menunggu Penilaian</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Kotak Feedback dari Dosen --}}
                                @if ($grade !== null && !empty($grade->feedback))
                                    <div class="mt-2.5 p-3.5 rounded-lg bg-slate-50 border border-slate-200/80 text-xs text-slate-700">
                                        <div class="flex items-center justify-between text-slate-500 mb-1.5 font-mono text-[11px]">
                                            <div class="flex items-center gap-1.5 font-sans font-bold text-slate-800">
                                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.502 49.188 49.188 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                                                </svg>
                                                <span>Umpan Balik Dosen ({{ $grade->grader->name ?? 'Dosen Pengampu' }}):</span>
                                            </div>
                                            @if ($grade->graded_at)
                                                <span>{{ $grade->graded_at->format('d M Y, H:i') }}</span>
                                            @endif
                                        </div>
                                        <p class="text-slate-800 leading-relaxed font-normal whitespace-pre-line pl-5 italic">
                                            &ldquo;{{ $grade->feedback }}&rdquo;
                                        </p>
                                    </div>
                                @elseif ($grade !== null)
                                    <div class="text-[11px] text-slate-400 font-mono italic">
                                        Tidak ada catatan tertulis dari dosen untuk tugas ini.
                                    </div>
                                @else
                                    <div class="text-[11px] text-amber-700 font-mono italic">
                                        Tugas telah dikumpulkan dan sedang dalam antrean evaluasi dosen pengampu.
                                    </div>
                                @endif

                                {{-- Link Navigasi Cepat ke Berkas Pengumpulan --}}
                                <div class="pt-2 flex items-center justify-between text-xs">
                                    <span class="text-slate-400 text-[11px]">
                                        ID Pengumpulan: <span class="font-mono">#{{ $sub->id }}</span>
                                    </span>
                                    <a href="{{ route('submissions.show', $sub) }}" class="inline-flex items-center gap-1 font-semibold text-slate-700 hover:text-slate-950 transition-colors">
                                        <span>Lihat Berkas & Detail Pengumpulan</span>
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-8 text-center text-xs text-slate-500 font-mono">
                        Belum ada tugas yang dikumpulkan untuk mata kuliah ini.
                    </div>
                @endif
            </div>

        @empty
            {{-- Empty State: Belum Ada Mata Kuliah / Nilai --}}
            <div class="lms-card p-12 text-center">
                <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3.5">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 mb-1">
                    Belum Ada Riwayat Tugas atau Nilai
                </h3>
                <p class="text-xs text-slate-600 max-w-sm mx-auto mb-5 leading-relaxed">
                    Anda belum mengumpulkan berkas tugas pada mata kuliah mana pun atau tugas yang Anda kumpulkan sedang menunggu penilaian dosen.
                </p>
                <a href="{{ route('courses.index') }}" class="btn-primary text-xs py-2 px-4 inline-flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                    </svg>
                    <span>Jelajahi Mata Kuliah Saya</span>
                </a>
            </div>
        @endforelse

    </div>

</x-layout>

