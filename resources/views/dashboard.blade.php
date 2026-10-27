<x-layout title="Dashboard">

    {{-- ====================================================================
         DASHBOARD EDITORIAL WORKSPACE (Linear & Notion Minimalist Protocol)
         Arsitektur: Flat Bento Layout (Main 70% + Context 30%)
         Adaptif: Menyesuaikan tampilan berdasarkan Role Aktif (Mahasiswa/Dosen/Admin/All)
         ==================================================================== --}}
    <div class="flex flex-col xl:flex-row gap-6 items-start">
        
        {{-- ================================================================
             AREA UTAMA: Main Editorial Workspace
             ================================================================ --}}
        <div class="flex-1 min-w-0 w-full space-y-6">
            
            {{-- Header: Judul & Search Input --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                        Dashboard Perkuliahan
                    </h1>
                    <p class="text-xs text-slate-600 mt-0.5">
                        Semester Ganjil 2026/2027
                    </p>
                </div>

                {{-- Search Bar Utilitarian --}}
                <div class="relative w-full sm:w-72">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500" aria-hidden="true">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        id="dashboard-search-input"
                        aria-label="Cari mata kuliah atau tugas"
                        onkeydown="if(event.key === 'Enter'){ window.location.href = '{{ route('courses.index') }}'; }"
                        placeholder="Cari mata kuliah atau tugas..." 
                        class="w-full pl-8.5 pr-12 py-1.5 bg-white rounded-lg border border-slate-300 text-xs text-slate-900 placeholder:text-slate-500 focus:outline-none focus:border-slate-900 transition-colors"
                    >
                    <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none" aria-hidden="true">
                        <kbd class="text-[11px] font-mono text-slate-600 bg-slate-50 border border-slate-200 px-1 py-0.5 rounded">
                            ⌘K
                        </kbd>
                    </div>
                </div>
            </div>

            {{-- Editorial Greeting & Swiss KPI Ledger (Role Tailored) --}}
            <div class="lms-card overflow-hidden">
                <div class="p-6 pb-5 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-200/80">
                    <div class="flex items-center gap-4 sm:gap-5 min-w-0">
                        {{-- In-Component Hero: Open Doodles Coffee with Grounding Shadow --}}
                        <div class="relative flex-shrink-0 flex flex-col items-center select-none" id="dashboard-coffee-hero">
                            <img src="{{ asset('images/illustrations/opendoodles-coffee.svg') }}"
                                 alt=""
                                 aria-hidden="true"
                                 class="w-20 sm:w-24 md:w-28 h-auto diorama-doodle object-contain filter drop-shadow-2xs"
                                 loading="lazy"
                                 decoding="async">
                            <div class="w-16 sm:w-20 md:w-24 h-1.5 rounded-full pointer-events-none -mt-1"
                                 style="background: radial-gradient(ellipse at center, rgba(47, 52, 55, 0.22) 0%, rgba(47, 52, 55, 0) 70%);"></div>
                        </div>

                        <div class="min-w-0">
                            <h2 class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight mt-0.5">
                                @if ($activeRole === 'dosen')
                                    Selamat datang, {{ $currentUser->name ?? 'Bapak/Ibu Dosen' }}
                                @elseif ($activeRole === 'admin')
                                    Selamat datang, Administrator {{ $currentUser->name ?? '' }}
                                @else
                                    Selamat datang, {{ $currentUser->name ?? 'Mahasiswa' }}
                                @endif
                            </h2>
                            <p class="text-xs text-slate-500 mt-0.5">
                                @if ($activeRole === 'dosen')
                                    Manajemen silabus, penugasan kelas, dan evaluasi mahasiswa.
                                @elseif ($activeRole === 'admin')
                                    Manajemen master data pengguna dan kurikulum akademik.
                                @else
                                    Kurikulum aktif, materi bahan ajar, dan status penugasan.
                                @endif
                            </p>
                        </div>
                    </div>

                    {{-- Role Specific Quick Action Buttons --}}
                    <div class="flex items-center gap-2 flex-shrink-0">
                        @if ($activeRole === 'dosen')
                            <a href="{{ route('courses.index') }}" class="btn-outline text-xs py-2 greeting-action-btn" data-tactile="spring">
                                <span>Kelas Saya</span>
                            </a>
                        @elseif ($activeRole === 'admin')
                            <a href="{{ route('users.create') }}" class="btn-primary text-xs py-2 greeting-action-btn" data-tactile="spring">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                <span>Tambah Pengguna</span>
                            </a>
                            <a href="{{ route('courses.create') }}" class="btn-outline text-xs py-2 greeting-action-btn" data-tactile="spring">
                                <span>+ Mata Kuliah</span>
                            </a>
                        @else
                            <a href="{{ route('courses.index') }}" class="btn-primary text-xs py-2 greeting-action-btn" data-tactile="spring">
                                <span>Mata Kuliah Saya</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                </svg>
                            </a>
                            <a href="{{ route('tentang') }}" class="btn-outline text-xs py-2 greeting-action-btn" data-tactile="spring">
                                <span>Info LMS</span>
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Satu sumber kebenaran metrik: Swiss Actionable KPI Ledger (3 Kolom Vertikal Rapi, Zero Vanity Metrics) --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-slate-200/80 bg-slate-50/50 text-xs">
                    @if ($activeRole === 'dosen')
                        <div class="p-4 sm:px-6">
                            <span class="text-slate-500 block font-medium text-xs">Perlu Dinilai</span>
                            <div class="mt-1 flex items-baseline gap-1.5 whitespace-nowrap">
                                <span class="text-xl font-extrabold font-mono text-slate-900 tracking-tight">{{ $ungradedSubmissionsCount }}</span>
                                <span class="text-xs font-semibold text-slate-600">Berkas</span>
                            </div>
                            <span class="mt-1 block text-[11px] font-mono truncate {{ $ungradedSubmissionsCount > 0 ? 'text-amber-800 font-semibold' : 'text-slate-500' }}">
                                {{ $ungradedSubmissionsCount > 0 ? 'Menunggu pemeriksaan nilai' : 'Semua berkas selesai dinilai' }}
                            </span>
                        </div>
                        <div class="p-4 sm:px-6">
                            <span class="text-slate-500 block font-medium text-xs">Progres Penilaian</span>
                            <div class="mt-1 flex items-baseline gap-1.5 whitespace-nowrap">
                                <span class="text-xl font-extrabold font-mono text-slate-900 tracking-tight">{{ $gradedSubmissionsCount }} <span class="text-slate-400 font-normal">/</span> {{ $totalSubmissions }}</span>
                                <span class="text-xs font-semibold text-slate-600">Berkas</span>
                            </div>
                            <span class="mt-1 block text-[11px] font-mono text-slate-500 truncate">
                                {{ $totalSubmissions > 0 ? round(($gradedSubmissionsCount / $totalSubmissions) * 100) . '% berkas telah dinilai' : 'Belum ada pengumpulan masuk' }}
                            </span>
                        </div>
                        <div class="p-4 sm:px-6">
                            <span class="text-slate-500 block font-medium text-xs">Beban Mengajar</span>
                            <div class="mt-1 flex items-baseline gap-1.5 whitespace-nowrap">
                                <span class="text-xl font-extrabold font-mono text-slate-900 tracking-tight">{{ $activeCoursesCount }}</span>
                                <span class="text-xs font-semibold text-slate-600">Kelas Aktif</span>
                            </div>
                            <span class="mt-1 block text-[11px] font-mono text-slate-500 truncate">
                                {{ $publishedAssignmentsCount }} tugas terbit{{ $draftAssignmentsCount > 0 ? ' · ' . $draftAssignmentsCount . ' draft' : '' }}
                            </span>
                        </div>
                    @elseif ($activeRole === 'admin')
                        <div class="p-4 sm:px-6">
                            <span class="text-slate-500 block font-medium text-xs">Direktori Pengguna</span>
                            <div class="mt-1 flex items-baseline gap-1.5 whitespace-nowrap">
                                <span class="text-xl font-extrabold font-mono text-slate-900 tracking-tight">{{ $totalUsersCount }}</span>
                                <span class="text-xs font-semibold text-slate-600">Akun</span>
                            </div>
                            <span class="mt-1 block text-[11px] font-mono text-slate-500 truncate">
                                {{ $totalMahasiswaUsersCount }} mahasiswa · {{ $totalDosenUsersCount }} dosen
                            </span>
                        </div>
                        <div class="p-4 sm:px-6">
                            <span class="text-slate-500 block font-medium text-xs">Status Kurikulum</span>
                            <div class="mt-1 flex items-baseline gap-1.5 whitespace-nowrap">
                                <span class="text-xl font-extrabold font-mono text-slate-900 tracking-tight">{{ $activeCoursesCount }}</span>
                                <span class="text-xs font-semibold text-slate-600">Kelas Aktif</span>
                            </div>
                            <span class="mt-1 block text-[11px] font-mono text-slate-500 truncate">
                                {{ $draftCoursesCount }} draft · {{ $archivedCoursesCount }} diarsipkan
                            </span>
                        </div>
                        <div class="p-4 sm:px-6">
                            <span class="text-slate-500 block font-medium text-xs">Partisipasi Kelas (Enrollment)</span>
                            <div class="mt-1 flex items-baseline gap-1.5 whitespace-nowrap">
                                <span class="text-xl font-extrabold font-mono text-slate-900 tracking-tight">{{ $totalStudents }}</span>
                                <span class="text-xs font-semibold text-slate-600">Mhs Terdaftar</span>
                            </div>
                            <span class="mt-1 block text-[11px] font-mono text-slate-500 truncate">
                                {{ $totalEnrollmentsCount }} total pendaftaran lintas kelas
                            </span>
                        </div>
                    @else
                        <div class="p-4 sm:px-6">
                            <span class="text-slate-500 block font-medium text-xs">Beban Studi Aktif</span>
                            <div class="mt-1 flex items-baseline gap-1.5 whitespace-nowrap">
                                <span class="text-xl font-extrabold font-mono text-slate-900 tracking-tight">{{ $activeCoursesCount }}</span>
                                <span class="text-xs font-semibold text-slate-600">Kelas</span>
                            </div>
                            <span class="mt-1 block text-[11px] font-mono text-slate-500 truncate">
                                Total {{ $studentActiveSks }} SKS semester ini
                            </span>
                        </div>
                        <div class="p-4 sm:px-6">
                            <span class="text-slate-500 block font-medium text-xs">Tugas Belum Dikumpul</span>
                            <div class="mt-1 flex items-baseline gap-1.5 whitespace-nowrap">
                                <span class="text-xl font-extrabold font-mono text-slate-900 tracking-tight">{{ $studentUnsubmittedCount }}</span>
                                <span class="text-xs font-semibold text-slate-600">Tugas</span>
                            </div>
                            <span class="mt-1 block text-[11px] font-mono truncate {{ $studentUnsubmittedCount > 0 ? 'text-amber-800 font-semibold' : 'text-emerald-800 font-semibold' }}">
                                {{ $studentUnsubmittedCount > 0 ? 'Perlu segera dikerjakan' : 'Semua tugas telah terkumpul' }}
                            </span>
                        </div>
                        <div class="p-4 sm:px-6">
                            <span class="text-slate-500 block font-medium text-xs">Rata-Rata Nilai</span>
                            <div class="mt-1 flex items-baseline gap-1.5 whitespace-nowrap">
                                <span class="text-xl font-extrabold font-mono text-slate-900 tracking-tight">{{ $studentAverageScore !== null ? $studentAverageScore : '—' }}</span>
                                @if ($studentAverageScore !== null)
                                    <span class="text-xs font-semibold text-slate-600">/ 100</span>
                                @endif
                            </div>
                            <span class="mt-1 block text-[11px] font-mono text-slate-500 truncate">
                                Dari {{ $studentGradedCount }} tugas yang telah dinilai
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            @if ($activeRole === 'admin')
                {{-- ADMIN STREAMLINED WORKSPACE: Satu Tabel Tata Kelola Kurikulum & Enrollment (F3 & F4) tanpa duplikasi kotak --}}
                <div class="lms-card overflow-hidden">
                    <div class="p-5 border-b border-slate-200/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div>
                            <h2 class="section-title">Tata kelola kurikulum & enrollment</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Ringkasan seluruh mata kuliah, dosen pengampu, dan kuota pendaftaran mahasiswa</p>
                        </div>
                        <a href="{{ route('courses.index') }}" class="text-xs font-semibold text-slate-700 hover:text-slate-950 transition-colors inline-flex items-center gap-1 group">
                            <span>Katalog & Filter Lengkap</span>
                            <svg class="w-3 h-3 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                                    <th class="py-3 px-4">Mata Kuliah</th>
                                    <th class="py-3 px-4">Dosen Pengampu</th>
                                    <th class="py-3 px-4 text-center">Enrollment</th>
                                    <th class="py-3 px-4 text-center">Konten</th>
                                    <th class="py-3 px-4 text-center">Status</th>
                                    <th class="py-3 px-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($allCourses as $course)
                                    <tr class="hover:bg-slate-50/70 transition-colors">
                                        <td class="py-3 px-4">
                                            <div class="flex items-center gap-2">
                                                <span class="font-mono text-[11px] font-bold text-slate-800 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200/90 shrink-0">
                                                    {{ $course->code }}
                                                </span>
                                                <a href="{{ route('courses.show', $course) }}" class="font-bold text-slate-900 hover:text-slate-700 truncate">
                                                    {{ $course->name }}
                                                </a>
                                                <span class="text-[11px] font-mono text-slate-400 shrink-0">({{ $course->sks }} SKS)</span>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 text-slate-700">
                                            {{ $course->lecturer->name ?? 'Belum ditugaskan' }}
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <span class="font-mono font-semibold text-slate-800">{{ $course->students_count }}</span>
                                            <span class="text-slate-500">mhs</span>
                                        </td>
                                        <td class="py-3 px-4 text-center font-mono text-[11px] text-slate-600">
                                            {{ $course->materials_count }} materi · {{ $course->assignments_count }} tugas
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            @if ($course->status === 'active')
                                                <span class="status status-active"><span class="status-dot" aria-hidden="true"></span>Aktif</span>
                                            @elseif ($course->status === 'draft')
                                                <span class="status status-draft"><span class="status-dot" aria-hidden="true"></span>Draft</span>
                                            @else
                                                <span class="status status-archived"><span class="status-dot" aria-hidden="true"></span>Arsip</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-right whitespace-nowrap">
                                            <div class="inline-flex items-center gap-1.5">
                                                <a href="{{ route('courses.show', $course) }}" class="text-[11px] font-semibold text-slate-700 hover:text-slate-950 px-2 py-1 bg-slate-50 hover:bg-slate-100 rounded border border-slate-200 transition-colors">
                                                    Kelola Kelas
                                                </a>
                                                <a href="{{ route('courses.edit', $course) }}" class="text-[11px] font-semibold text-slate-600 hover:text-slate-900 px-2 py-1 hover:bg-slate-100 rounded transition-colors">
                                                    Edit
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-slate-500">
                                            Belum ada mata kuliah yang terdaftar di sistem.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                {{-- SECTION: Mata Kuliah (Dosen & Mahasiswa) --}}
                <div>
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <h2 class="section-title">
                                @if ($activeRole === 'dosen')
                                    Mata kuliah yang diampu
                                @else
                                    Mata kuliah semester ini
                                @endif
                            </h2>
                        </div>

                        <div class="flex items-center gap-4">
                            <a href="{{ route('courses.index') }}" class="text-xs font-semibold text-slate-700 hover:text-slate-950 transition-colors inline-flex items-center gap-1 group">
                                <span>Lihat Semua</span>
                                <svg class="w-3 h-3 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                </svg>
                            </a>
                        </div>
                    </div>

                    @if($courses->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            @foreach($courses as $course)
                                @php
                                    $materialCount = $course->materials ? $course->materials->count() : 0;
                                    $assignmentCount = $course->assignments ? $course->assignments->count() : 0;
                                @endphp

                                {{-- Clean Two-Tone Swiss Index Card (Zero Left-Spine) --}}
                                <a href="{{ route('courses.show', $course) }}" class="two-tone-card group block" aria-label="Mata kuliah {{ $course->name }}, {{ $course->code }}">
                                    <div class="two-tone-body">
                                        <div class="flex items-center justify-between gap-2 mb-2.5">
                                            <span class="font-mono text-[11px] font-bold text-slate-800 bg-slate-100 px-2 py-0.5 rounded border border-slate-200/90">
                                                {{ $course->code }}
                                            </span>
                                            <span class="font-mono text-xs text-slate-500 font-semibold">
                                                {{ $course->sks }} SKS
                                            </span>
                                        </div>

                                        <h3 class="font-bold text-sm text-slate-900 group-hover:text-slate-700 transition-colors line-clamp-1 mb-1">
                                            {{ $course->name }}
                                        </h3>

                                        <p class="text-xs text-slate-600 font-normal truncate">
                                            {{ $course->lecturer->name ?? 'Dosen Belum Ditugaskan' }}
                                        </p>
                                    </div>

                                    {{-- Two-Tone Footer: Jumlah materi & tugas + status + panah interaktif --}}
                                    <div class="two-tone-footer">
                                        <span class="text-slate-600 font-mono text-[11px]">
                                            {{ $materialCount }} materi <span aria-hidden="true">·</span> {{ $assignmentCount }} tugas
                                        </span>
                                        <div class="flex items-center gap-2">
                                            @if ($course->status === 'active')
                                                <span class="status status-active"><span class="status-dot" aria-hidden="true"></span>Aktif</span>
                                            @elseif ($course->status === 'draft')
                                                <span class="status status-draft"><span class="status-dot" aria-hidden="true"></span>Draft</span>
                                            @else
                                                <span class="status status-archived"><span class="status-dot" aria-hidden="true"></span>Diarsipkan</span>
                                            @endif
                                            <svg class="w-3.5 h-3.5 text-slate-400 group-hover:translate-x-0.5 group-hover:text-[#FE774C] transition-all" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                            </svg>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="lms-card p-8 text-center">
                            <p class="text-xs text-slate-600 mb-3">Belum ada mata kuliah aktif yang terdaftar.</p>
                            @if ($activeRole !== 'mahasiswa')
                                <a href="{{ route('courses.create') }}" class="btn-primary text-xs">
                                    Tambah Mata Kuliah
                                </a>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- SECTION: 2 Kolom Adaptif (Dosen & Mahasiswa) --}}
                <div class="grid grid-cols-1 {{ $activeRole === 'mahasiswa' ? '' : 'lg:grid-cols-2' }} gap-5">
                    
                    {{-- Modul 1: Actionable Task Module (Dosen / Mahasiswa) --}}
                    <div class="lms-card p-5">
                        @if ($activeRole === 'dosen')
                            {{-- DOSEN: Antrean Pemeriksaan & Penilaian Tugas Mahasiswa --}}
                            <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                                <div>
                                    <h2 class="section-title">Antrean penilaian tugas</h2>
                                </div>
                                <a href="{{ route('dosen.submissions.index') }}" class="text-xs font-semibold text-slate-700 hover:text-slate-950 flex items-center gap-1">
                                    Semua submission →
                                </a>
                            </div>

                            <div class="divide-y divide-slate-100">
                                @forelse($pendingSubmissions as $sub)
                                    @php
                                        $isGraded = (bool) $sub->grade;
                                    @endphp
                                    <div class="py-2.5 flex items-center justify-between gap-3 group">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <h3 class="text-xs font-bold text-slate-900 group-hover:text-slate-700 transition-colors truncate">
                                                    {{ $sub->student->name ?? 'Mahasiswa' }}
                                                </h3>
                                                @if($sub->is_late)
                                                    <span class="text-[10px] font-mono text-rose-600 bg-rose-50 px-1.5 py-0.2 rounded border border-rose-200 shrink-0">Terlambat</span>
                                                @endif
                                            </div>
                                            <p class="text-xs text-slate-600 mt-0.5 truncate">
                                                <span class="font-medium text-slate-800">{{ $sub->assignment->title ?? 'Tugas' }}</span>
                                                <span aria-hidden="true">•</span>
                                                <span class="text-slate-500">{{ $sub->assignment->course->name ?? 'Mata Kuliah' }}</span>
                                            </p>
                                        </div>

                                        <div class="flex items-center gap-2 flex-shrink-0">
                                            @if($isGraded)
                                                <span class="status status-ontime">
                                                    <span class="status-dot" aria-hidden="true"></span>Nilai: {{ number_format($sub->grade->score, 0) }}
                                                </span>
                                            @else
                                                <span class="status status-draft">
                                                    <span class="status-dot" aria-hidden="true"></span>Perlu dinilai
                                                </span>
                                            @endif
                                            <a href="{{ route('submissions.show', $sub->id) }}" class="text-[11px] font-semibold text-slate-700 hover:text-slate-950 px-2 py-1 bg-slate-50 hover:bg-slate-100 rounded border border-slate-200 transition-colors">
                                                {{ $isGraded ? 'Lihat' : 'Nilai' }}
                                            </a>
                                        </div>
                                    </div>
                                @empty
                                    <div class="py-6 text-center text-slate-500 text-xs">
                                        <svg class="w-8 h-8 mx-auto text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Belum ada pengumpulan tugas baru yang menunggu penilaian.
                                    </div>
                                @endforelse
                            </div>

                        @else
                            {{-- MAHASISWA: Status Pengumpulan Tugas Mandiri (Action-Oriented) --}}
                            <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                                <div>
                                    <h2 class="section-title">Status pengerjaan tugas saya</h2>
                                    <p class="text-[11px] text-slate-500 mt-0.5">Pantau status submit dan nilai setiap tugas</p>
                                </div>
                                <a href="{{ route('mahasiswa.submissions.index') }}" class="text-xs font-semibold text-slate-700 hover:text-slate-950 flex items-center gap-1">
                                    Riwayat saya →
                                </a>
                            </div>

                            <div class="divide-y divide-slate-100">
                                @forelse($studentTasks as $hw)
                                    @php
                                        $userSub = $hw->submissions->first();
                                        $isSubmitted = !is_null($userSub);
                                        $hasGrade = $isSubmitted && !is_null($userSub->grade);
                                        $isDuePassed = $hw->due_at && \Carbon\Carbon::parse($hw->due_at)->isPast();
                                    @endphp
                                    <div class="py-2.5 flex items-center justify-between gap-3 group">
                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-xs font-bold text-slate-900 group-hover:text-slate-700 transition-colors truncate">
                                                {{ $hw->title }}
                                            </h3>
                                            <p class="text-xs text-slate-600 mt-0.5 truncate">
                                                <span class="text-slate-800 font-medium">{{ $hw->course->name ?? 'Mata Kuliah' }}</span>
                                                <span aria-hidden="true">•</span>
                                                <span class="font-mono text-slate-500">Tenggat: {{ $hw->due_at ? \Carbon\Carbon::parse($hw->due_at)->format('d M, H:i') : 'Fleksibel' }}</span>
                                            </p>
                                        </div>

                                        <div class="flex items-center gap-2 flex-shrink-0">
                                            @if($hasGrade)
                                                <span class="status status-ontime">
                                                    <span class="status-dot" aria-hidden="true"></span>Nilai: {{ number_format($userSub->grade->score, 0) }}
                                                </span>
                                            @elseif($isSubmitted)
                                                <span class="status status-ontime">
                                                    <span class="status-dot" aria-hidden="true"></span>Dikumpulkan
                                                </span>
                                            @elseif($isDuePassed)
                                                <span class="status status-late">
                                                    <span class="status-dot" aria-hidden="true"></span>Lewat tenggat
                                                </span>
                                            @else
                                                <span class="status status-draft">
                                                    <span class="status-dot" aria-hidden="true"></span>Belum kirim
                                                </span>
                                            @endif

                                            @if($isSubmitted)
                                                <a href="{{ route('submissions.show', $userSub->id) }}" class="text-[11px] font-semibold text-slate-700 hover:text-slate-950 px-2.5 py-1 bg-slate-50 hover:bg-slate-100 rounded border border-slate-200 transition-colors">
                                                    Bukti
                                                </a>
                                            @else
                                                <a href="{{ route('assignments.show', $hw->id) }}" class="text-[11px] font-semibold text-white bg-slate-900 hover:bg-slate-800 px-2.5 py-1 rounded transition-colors">
                                                    Kumpul
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="py-6 text-center text-slate-500 text-xs">
                                        Tidak ada penugasan aktif yang perlu dikerjakan saat ini.
                                    </div>
                                @endforelse
                            </div>
                        @endif
                    </div>

                    {{-- Modul 2: Progres Pengumpulan Kelas untuk Dosen --}}
                    @if ($activeRole === 'dosen')
                        <div class="lms-card p-5">
                            <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                                <div>
                                    <h2 class="section-title">Progres pengumpulan tugas</h2>
                                    <p class="text-xs text-slate-500 mt-0.5">Tingkat partisipasi pengumpulan per tugas kelas</p>
                                </div>
                                <span class="text-xs font-mono text-slate-600 font-medium">{{ $lecturerAssignmentsProgress->count() }} Tugas</span>
                            </div>

                            <div class="divide-y divide-slate-100 text-xs">
                                @forelse($lecturerAssignmentsProgress as $taskItem)
                                    @php
                                        $enrolledCount = $taskItem->course?->students_count ?? 0;
                                        $submittedCount = $taskItem->submissions_count ?? 0;
                                    @endphp
                                    <div class="py-2.5 flex items-center justify-between gap-3">
                                        <div class="flex-1 min-w-0">
                                            <a href="{{ route('assignments.show', $taskItem->id) }}" class="font-semibold text-slate-900 hover:text-slate-700 block truncate transition-colors">
                                                {{ $taskItem->title }}
                                            </a>
                                            <span class="text-[11px] text-slate-500 block truncate">
                                                <span class="font-medium text-slate-600">{{ $taskItem->course->name ?? 'Mata Kuliah' }}</span> · <span class="font-mono">Tenggat {{ \Carbon\Carbon::parse($taskItem->due_at)->format('d M') }}</span>
                                            </span>
                                        </div>
                                        <div class="text-right flex-shrink-0">
                                            <a href="{{ route('assignments.show', $taskItem->id) }}" class="inline-flex items-center gap-1.5 font-mono font-bold text-slate-900 bg-slate-100 hover:bg-slate-200/80 border border-slate-200 px-2 py-1 rounded transition-colors">
                                                <span>{{ $submittedCount }} / {{ $enrolledCount }}</span>
                                                <span class="text-[10px] font-sans font-normal text-slate-600">kumpul</span>
                                            </a>
                                        </div>
                                    </div>
                                @empty
                                    <div class="py-4 text-center text-slate-600 text-xs">
                                        Belum ada penugasan pada kelas yang diampu.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endif

                </div>
            @endif

        </div>

        {{-- ================================================================
             AREA KANAN: Khusus Admin (Direktori Akun Terbaru F2)
             (Sidebar "Tenggat & jadwal" dihapus untuk Dosen & Mahasiswa karena redundan)
             ================================================================ --}}
        @if ($activeRole === 'admin')
            <div class="w-full xl:w-72 flex-shrink-0 space-y-5">
                {{-- ADMIN RIGHT PANEL: Direktori Pengguna Terbaru (F2) --}}
                <div class="lms-card p-5">
                    <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                        <div>
                            <h2 class="section-title">Akun terbaru</h2>
                            <p class="text-[11px] text-slate-500 mt-0.5">Direktori pengguna terdaftar</p>
                        </div>
                        <a href="{{ route('users.index') }}" class="text-xs font-semibold text-slate-700 hover:text-slate-950">
                            Kelola →
                        </a>
                    </div>

                    <div class="divide-y divide-slate-100 text-xs">
                        @forelse($recentUsers as $u)
                            <div class="py-2.5 flex items-center justify-between gap-2.5">
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('users.edit', $u) }}" class="font-semibold text-slate-900 hover:text-slate-700 block truncate transition-colors">
                                        {{ $u->name }}
                                    </a>
                                    <span class="text-[11px] text-slate-500 font-mono block truncate">{{ $u->email }}</span>
                                </div>
                                <div class="flex-shrink-0">
                                    @if($u->role === 'dosen')
                                        <span class="status status-dosen"><span class="status-dot" aria-hidden="true"></span>Dosen</span>
                                    @elseif($u->role === 'admin')
                                        <span class="status status-admin"><span class="status-dot" aria-hidden="true"></span>Admin</span>
                                    @else
                                        <span class="status status-mahasiswa"><span class="status-dot" aria-hidden="true"></span>Mhs</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="py-4 text-center text-slate-600 text-xs">
                                Belum ada data pengguna.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-layout>
