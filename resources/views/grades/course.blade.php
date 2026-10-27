<x-layout title="Rekap Nilai Kelas">

    {{-- Navigasi Balik & Action Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <a href="{{ route('courses.show', $course) }}" class="btn-outline text-xs py-2 w-fit inline-flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-600" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            <span>Kembali ke Detail Mata Kuliah</span>
        </a>

        <div class="flex items-center gap-2 text-xs text-slate-500 font-mono">
            <span>Peran:</span>
            <span class="status status-dosen font-bold">
                <span class="status-dot" aria-hidden="true"></span>Dosen Pengampu
            </span>
        </div>
    </div>

    {{-- Card Header: Informasi Mata Kuliah --}}
    <div class="lms-card p-6 sm:p-7 mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="font-mono text-xs font-bold px-2.5 py-0.5 rounded-md bg-slate-100 text-slate-800 border border-slate-200">
                        {{ $course->code }}
                    </span>
                    <span class="font-mono text-xs font-semibold px-2.5 py-0.5 rounded-md bg-slate-100 text-slate-800 border border-slate-200">
                        {{ $course->sks }} SKS
                    </span>
                    @if ($course->status === 'active')
                        <span class="status status-active"><span class="status-dot" aria-hidden="true"></span>Aktif</span>
                    @elseif ($course->status === 'draft')
                        <span class="status status-draft"><span class="status-dot" aria-hidden="true"></span>Draft</span>
                    @else
                        <span class="status status-archived"><span class="status-dot" aria-hidden="true"></span>Diarsipkan</span>
                    @endif
                </div>

                <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">
                    Rekap Nilai Kelas: {{ $course->name }}
                </h1>
                <p class="text-slate-600 text-xs sm:text-sm mt-1 max-w-3xl leading-relaxed">
                    Matriks ledger evaluasi mahasiswa, transparansi akumulasi skor tugas, dan pemantauan pengumpulan penugasan secara terpadu.
                </p>
            </div>

            <div class="flex items-center gap-3 p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 shrink-0 self-start sm:self-auto">
                <div class="w-10 h-10 rounded-lg bg-slate-900 text-white flex items-center justify-center font-bold text-xs font-mono">
                    {{ strtoupper(substr($course->lecturer->name ?? 'D', 0, 2)) }}
                </div>
                <div class="text-xs">
                    <p class="font-bold text-slate-900 leading-tight">{{ $course->lecturer->name ?? 'Dosen Pengampu' }}</p>
                    <p class="text-slate-500 font-mono text-[11px] mt-0.5">{{ $course->lecturer->nim_nip ?? 'NIP: -' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Ringkasan Statistik Kelas (Bento Cards) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        
        {{-- Stat 1: Total Mahasiswa --}}
        <div class="two-tone-card">
            <div class="two-tone-body">
                <div class="flex items-center justify-between text-slate-500 mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider font-mono">Total Mahasiswa</span>
                    <div class="w-7 h-7 rounded-md bg-slate-100 text-slate-700 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                        </svg>
                    </div>
                </div>
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 font-mono tabular-nums tracking-tight">
                    {{ $totalStudents }}
                </div>
                <p class="text-xs text-slate-500 mt-1">Mahasiswa terdaftar di kelas</p>
            </div>
            <div class="two-tone-footer text-slate-600 font-mono text-[11px]">
                <span>Status Kelas</span>
                <span class="font-bold text-slate-900">{{ $totalStudents > 0 ? 'Siap Evaluasi' : 'Belum Ada Mahasiswa' }}</span>
            </div>
        </div>

        {{-- Stat 2: Total Tugas Terbit --}}
        <div class="two-tone-card">
            <div class="two-tone-body">
                <div class="flex items-center justify-between text-slate-500 mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider font-mono">Tugas Terbit</span>
                    <div class="w-7 h-7 rounded-md bg-sky-50 text-sky-700 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                    </div>
                </div>
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 font-mono tabular-nums tracking-tight">
                    {{ $totalAssignments }}
                </div>
                <p class="text-xs text-slate-500 mt-1">Item penugasan aktif</p>
            </div>
            <div class="two-tone-footer text-slate-600 font-mono text-[11px]">
                <span>Penugasan</span>
                <a href="{{ route('courses.assignments.create', $course) }}" class="font-bold text-sky-700 hover:underline">
                    + Tambah Tugas
                </a>
            </div>
        </div>

        {{-- Stat 3: Progres Evaluasi --}}
        <div class="two-tone-card">
            <div class="two-tone-body">
                <div class="flex items-center justify-between text-slate-500 mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider font-mono">Progres Penilaian</span>
                    <div class="w-7 h-7 rounded-md bg-amber-50 text-amber-700 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                        </svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-extrabold text-slate-900 font-mono tabular-nums tracking-tight">
                        {{ $totalGraded }}
                    </span>
                    <span class="text-xs font-mono font-semibold text-slate-400">/ {{ $totalSubmissions }} berkas</span>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    {{ $totalPending > 0 ? $totalPending . ' tugas menunggu penilaian' : 'Semua pengumpulan telah dinilai' }}
                </p>
            </div>
            <div class="two-tone-footer text-slate-600 font-mono text-[11px]">
                <span>Status Antrean</span>
                <span class="font-bold {{ $totalPending > 0 ? 'text-amber-700' : 'text-emerald-700' }}">
                    {{ $totalPending > 0 ? $totalPending . ' Pending' : 'Lengkap' }}
                </span>
            </div>
        </div>

        {{-- Stat 4: Rata-rata Kelas --}}
        <div class="two-tone-card">
            <div class="two-tone-body">
                <div class="flex items-center justify-between text-slate-500 mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider font-mono">Rata-rata Kelas</span>
                    <div class="w-7 h-7 rounded-md bg-[#EDF3EC] text-[#245228] flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                        </svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl sm:text-3xl font-extrabold text-slate-900 font-mono tabular-nums tracking-tight">
                        {{ $classAverage !== null ? number_format($classAverage, 1) : '-' }}
                    </span>
                    @if ($classAverage !== null)
                        <span class="text-xs font-mono font-semibold text-slate-400">/ 100</span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 mt-1">Rata-rata akumulasi nilai seluruh mahasiswa</p>
            </div>
            <div class="two-tone-footer text-slate-600 font-mono text-[11px]">
                <span>Indikator Nilai</span>
                <span class="font-bold text-slate-900">
                    @if ($classAverage === null) Tidak Ada Data
                    @elseif ($classAverage >= 85) Sangat Memuaskan (A)
                    @elseif ($classAverage >= 70) Baik (B)
                    @else Cukup (C)
                    @endif
                </span>
            </div>
        </div>

    </div>

    {{-- Matriks Rekap Nilai Kelas (Grade Ledger Table) --}}
    <div class="lms-card overflow-hidden">
        
        <div class="p-5 sm:p-6 bg-slate-50/70 border-b border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">
                    Matriks Evaluasi Akademik & Rekap Nilai
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Klik sel nilai atau tombol aksi cepat untuk memeriksa berkas pengumpulan dan memperbarui skor.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span class="font-mono text-[11px] font-semibold text-slate-500">
                    {{ $students->count() }} Mahasiswa · {{ $assignments->count() }} Tugas
                </span>
            </div>
        </div>

        @if ($students->isNotEmpty() && $assignments->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-100/70 text-slate-700 font-semibold uppercase tracking-wider text-[11px]">
                            {{-- Kolom Mahasiswa --}}
                            <th scope="col" class="py-3.5 px-4 sticky left-0 bg-slate-100/90 backdrop-blur-xs z-10 min-w-[220px] border-r border-slate-200/80">
                                Mahasiswa
                            </th>

                            {{-- Kolom tiap Tugas --}}
                            @foreach ($assignments as $assignment)
                                <th scope="col" class="py-3.5 px-3 min-w-[140px] text-center border-r border-slate-200/80">
                                    <div class="truncate font-bold text-slate-900 text-xs" title="{{ $assignment->title }}">
                                        {{ $assignment->title }}
                                    </div>
                                    <div class="flex items-center justify-center gap-1.5 mt-0.5 text-[10px] text-slate-500 font-mono font-normal">
                                        <span>Maks: <strong class="text-slate-800">{{ $assignment->max_score }}</strong> pts</span>
                                        <span class="text-slate-300">·</span>
                                        <span>{{ $assignment->due_at?->format('d/m') ?? '-' }}</span>
                                    </div>
                                </th>
                            @endforeach

                            {{-- Kolom Rata-rata Akhir --}}
                            <th scope="col" class="py-3.5 px-4 text-center min-w-[120px] bg-slate-100/90 font-bold text-slate-900">
                                Rata-rata Akhir
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @foreach ($students as $student)
                            <tr class="hover:bg-slate-50/80 transition-colors group">
                                
                                {{-- Identitas Mahasiswa (Sticky Left) --}}
                                <td class="py-3 px-4 sticky left-0 bg-white group-hover:bg-slate-50/80 transition-colors border-r border-slate-200/80 z-10">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-md bg-slate-100 border border-slate-200 text-slate-800 flex items-center justify-center font-bold text-[10px] font-mono shrink-0">
                                            {{ strtoupper(substr($student->name, 0, 2)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-slate-900 truncate leading-snug">
                                                {{ $student->name }}
                                            </p>
                                            <p class="text-[10px] text-slate-500 font-mono truncate">
                                                {{ $student->nim_nip ?? $student->email }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Sel Nilai per Tugas --}}
                                @foreach ($assignments as $assignment)
                                    @php
                                        $sub = $submissionMatrix[$student->id][$assignment->id] ?? null;
                                        $grade = $sub?->grade;
                                    @endphp

                                    <td class="py-3 px-3 text-center border-r border-slate-200/80">
                                        @if ($sub !== null && $grade !== null)
                                            {{-- Sudah Dikumpulkan & Sudah Dinilai --}}
                                            <div class="flex items-center justify-between gap-1 group/cell">
                                                <div class="flex-1 text-center">
                                                    <div class="flex items-baseline justify-center gap-0.5">
                                                        <span class="tabular-nums font-mono font-bold text-slate-900 text-sm">
                                                            {{ number_format($grade->score, 0) }}
                                                        </span>
                                                        <span class="text-slate-400 font-mono text-[10px]">
                                                            /{{ $assignment->max_score }}
                                                        </span>
                                                    </div>
                                                    @if ($sub->is_late)
                                                        <span class="text-[9px] font-semibold text-rose-600 block leading-tight">
                                                            Terlambat
                                                        </span>
                                                    @endif
                                                </div>

                                                {{-- Tombol Aksi Cepat Edit/Lihat Pengumpulan --}}
                                                <a href="{{ route('submissions.show', $sub) }}"
                                                   class="p-1 rounded text-slate-400 hover:text-slate-900 hover:bg-slate-200/60 transition-colors opacity-0 group-hover/cell:opacity-100 focus:opacity-100 shrink-0"
                                                   title="Buka pengumpulan & ubah nilai {{ $student->name }}"
                                                   aria-label="Lihat pengumpulan {{ $student->name }}">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                                                    </svg>
                                                </a>
                                            </div>

                                        @elseif ($sub !== null)
                                            {{-- Dikumpulkan tapi Belum Dinilai --}}
                                            <a href="{{ route('submissions.show', $sub) }}" 
                                               class="inline-flex items-center gap-1 px-2 py-1 rounded bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 text-[10px] font-semibold font-mono transition-colors"
                                               title="Beri nilai tugas {{ $student->name }}">
                                                <span>Beri Nilai</span>
                                                <svg class="w-3 h-3 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                                </svg>
                                            </a>

                                        @else
                                            {{-- Belum Mengumpulkan --}}
                                            <span class="text-slate-300 font-mono text-xs select-none" title="Belum mengumpulkan tugas">-</span>
                                        @endif
                                    </td>
                                @endforeach

                                {{-- Rata-rata Mahasiswa --}}
                                <td class="py-3 px-4 text-center">
                                    @php
                                        $avg = $studentAverages[$student->id] ?? null;
                                    @endphp

                                    @if ($avg !== null)
                                        <div class="flex items-baseline justify-center gap-0.5">
                                            <span class="tabular-nums font-mono font-bold text-slate-900 text-sm">
                                                {{ number_format($avg, 1) }}
                                            </span>
                                            <span class="text-slate-400 font-mono text-[10px]">/ 100</span>
                                        </div>
                                    @else
                                        <span class="text-slate-400 font-mono text-xs">-</span>
                                    @endif
                                </td>

                            </tr>
                        @endforeach
                    </tbody>

                    {{-- Footer Matriks: Rata-rata per Tugas --}}
                    <tfoot>
                        <tr class="bg-slate-100/80 border-t-2 border-slate-200 text-slate-800 font-semibold text-xs">
                            <td class="py-3 px-4 sticky left-0 bg-slate-100/95 backdrop-blur-xs font-bold border-r border-slate-200/80 z-10">
                                Rata-rata Tugas Kelas
                            </td>

                            @foreach ($assignments as $assignment)
                                <td class="py-3 px-3 text-center border-r border-slate-200/80 font-mono tabular-nums font-bold">
                                    @if ($assignmentAverages[$assignment->id] !== null)
                                        <span class="text-slate-900">{{ number_format($assignmentAverages[$assignment->id], 1) }}</span>
                                        <span class="text-slate-400 text-[10px] font-normal">/{{ $assignment->max_score }}</span>
                                    @else
                                        <span class="text-slate-400 font-normal">-</span>
                                    @endif
                                </td>
                            @endforeach

                            <td class="py-3 px-4 text-center font-mono tabular-nums font-extrabold text-slate-900 bg-slate-200/50">
                                {{ $classAverage !== null ? number_format($classAverage, 1) : '-' }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @elseif ($assignments->isEmpty())
            <div class="p-12 text-center">
                <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 mb-1">
                    Belum Ada Tugas Diterbitkan
                </h3>
                <p class="text-xs text-slate-600 max-w-sm mx-auto mb-4 leading-relaxed">
                    Mata kuliah ini belum memiliki tugas. Buat tugas baru terlebih dahulu untuk mulai menilai mahasiswa.
                </p>
                <a href="{{ route('courses.assignments.create', $course) }}" class="btn-primary text-xs py-2 px-4 inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Buat Tugas Baru</span>
                </a>
            </div>
        @else
            <div class="p-12 text-center">
                <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 mb-1">
                    Belum Ada Mahasiswa Terdaftar
                </h3>
                <p class="text-xs text-slate-600 max-w-sm mx-auto mb-4 leading-relaxed">
                    Daftarkan mahasiswa ke dalam kelas ini melalui halaman detail mata kuliah untuk melihat matriks rekap nilai.
                </p>
                <a href="{{ route('courses.show', $course) }}" class="btn-primary text-xs py-2 px-4 inline-flex items-center gap-1.5">
                    <span>Kembali ke Detail Mata Kuliah</span>
                </a>
            </div>
        @endif

    </div>

</x-layout>

