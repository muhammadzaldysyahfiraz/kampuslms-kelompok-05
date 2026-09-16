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
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
                        <span>Dashboard Perkuliahan</span>
                        @if ($activeRole === 'admin')
                            <span class="badge-coral text-[11px] py-0.5 px-2 font-normal">Panel Admin</span>
                        @elseif ($activeRole === 'dosen')
                            <span class="badge-sky text-[11px] py-0.5 px-2 font-normal">Panel Dosen</span>
                        @elseif ($activeRole === 'all')
                            <span class="badge-amber text-[11px] py-0.5 px-2 font-normal">Mode Evaluasi</span>
                        @else
                            <span class="badge-mint text-[11px] py-0.5 px-2 font-normal">Portal Mahasiswa</span>
                        @endif
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

            {{-- Editorial Greeting & Fast Action Bar (Role Tailored) --}}
            <div class="lms-card p-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-5 border-b border-slate-100">
                    <div>
                        <h2 class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight mt-0.5">
                            @if ($activeRole === 'dosen')
                                Selamat datang, {{ $currentUser->name ?? 'Bapak/Ibu Dosen' }}
                            @elseif ($activeRole === 'admin')
                                Selamat datang, Administrator {{ $currentUser->name ?? '' }}
                            @elseif ($activeRole === 'all')
                                Mode Evaluasi Praktikum (All Features)
                            @else
                                Selamat datang, {{ $currentUser->name ?? 'Mahasiswa' }}
                            @endif
                        </h2>
                        <p class="text-xs text-slate-600 mt-1 max-w-xl leading-relaxed">
                            @if ($activeRole === 'dosen')
                                Kelola silabus materi perkuliahan, terbitkan penugasan baru, dan pantau pengumpulan tugas mahasiswa secara terpadu.
                            @elseif ($activeRole === 'admin')
                                Pantau master data sistem KampusLMS, kelola akun pengguna, dan administrasi kurikulum program studi.
                            @elseif ($activeRole === 'all')
                                Seluruh modul praktikum aktif: CRUD Mata Kuliah, Manajemen Pengguna, dan pratinjau semua fitur antarmuka.
                            @else
                                Pantau kurikulum perkuliahan semester ini, cek materi bahan ajar, dan selesaikan seluruh penugasan akademik tepat waktu.
                            @endif
                        </p>
                    </div>

                    {{-- Role Specific Quick Action Buttons --}}
                    <div class="flex items-center gap-2 flex-shrink-0">
                        @if ($activeRole === 'dosen')
                            <a href="{{ route('courses.create') }}" class="btn-primary text-xs py-2">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                <span>Tambah Mata Kuliah</span>
                            </a>
                            <a href="{{ route('courses.index') }}" class="btn-outline text-xs py-2">
                                <span>Kelas Saya</span>
                            </a>
                        @elseif ($activeRole === 'admin')
                            <a href="{{ route('users.create') }}" class="btn-primary text-xs py-2">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                <span>Tambah Pengguna</span>
                            </a>
                            <a href="{{ route('courses.create') }}" class="btn-outline text-xs py-2">
                                <span>+ Mata Kuliah</span>
                            </a>
                        @elseif ($activeRole === 'all')
                            <a href="{{ route('courses.create') }}" class="btn-primary text-xs py-2">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                <span>Tambah MK</span>
                            </a>
                            <a href="{{ route('users.index') }}" class="btn-outline text-xs py-2">
                                <span>Kelola Pengguna</span>
                            </a>
                        @else
                            <a href="{{ route('courses.index') }}" class="btn-primary text-xs py-2">
                                <span>Mata Kuliah Saya</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                </svg>
                            </a>
                            <a href="{{ route('tentang') }}" class="btn-outline text-xs py-2">
                                <span>Info LMS</span>
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Clean Horizontal Fast Metric Counters (Tailored by Role) --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-4 text-xs">
                    @if ($activeRole === 'dosen')
                        <div>
                            <span class="text-slate-600 block font-medium text-xs">Mata Kuliah Diampu</span>
                            <span class="text-base font-bold font-mono text-slate-900 mt-0.5 block">{{ $courses->count() }} Kelas</span>
                        </div>
                        <div>
                            <span class="text-slate-600 block font-medium text-xs">Tugas Diterbitkan</span>
                            <span class="text-base font-bold font-mono text-slate-900 mt-0.5 block">{{ $totalAssignments }} Tugas</span>
                        </div>
                        <div>
                            <span class="text-slate-600 block font-medium text-xs">Pengumpulan Masuk</span>
                            <span class="text-base font-bold font-mono text-slate-900 mt-0.5 block">{{ $totalSubmissions }} Berkas</span>
                        </div>
                        <div>
                            <span class="text-slate-600 block font-medium text-xs">Mahasiswa Terdaftar</span>
                            <span class="text-base font-bold font-mono text-slate-900 mt-0.5 block">{{ $totalStudents }} Orang</span>
                        </div>
                    @elseif ($activeRole === 'admin')
                        <div>
                            <span class="text-slate-600 block font-medium text-xs">Total Pengguna</span>
                            <span class="text-base font-bold font-mono text-slate-900 mt-0.5 block">{{ $totalStudents + $totalLecturers + 1 }} Akun</span>
                        </div>
                        <div>
                            <span class="text-slate-600 block font-medium text-xs">Total Mata Kuliah</span>
                            <span class="text-base font-bold font-mono text-slate-900 mt-0.5 block">{{ $totalCourses }} MK</span>
                        </div>
                        <div>
                            <span class="text-slate-600 block font-medium text-xs">Modul Bahan Ajar</span>
                            <span class="text-base font-bold font-mono text-slate-900 mt-0.5 block">{{ $totalMaterials }} Materi</span>
                        </div>
                        <div>
                            <span class="text-slate-600 block font-medium text-xs">Status Sistem</span>
                            <span class="text-base font-bold font-mono text-emerald-800 mt-0.5 block">Normal (100%)</span>
                        </div>
                    @else
                        <div>
                            <span class="text-slate-600 block font-medium text-xs">SKS Diambil</span>
                            <span class="text-base font-bold font-mono text-slate-900 mt-0.5 block">{{ $courses->sum('sks') }} SKS</span>
                        </div>
                        <div>
                            <span class="text-slate-600 block font-medium text-xs">Mata Kuliah Aktif</span>
                            <span class="text-base font-bold font-mono text-slate-900 mt-0.5 block">{{ $courses->where('status', 'active')->count() }} Kelas</span>
                        </div>
                        <div>
                            <span class="text-slate-600 block font-medium text-xs">Tugas Perkuliahan</span>
                            <span class="text-base font-bold font-mono text-slate-900 mt-0.5 block">{{ $homeworks->count() }} Tugas</span>
                        </div>
                        <div>
                            <span class="text-slate-600 block font-medium text-xs">Dosen Pengampu</span>
                            <span class="text-base font-bold font-mono text-slate-900 mt-0.5 block">{{ $totalLecturers }} Dosen</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- SECTION: Mata Kuliah --}}
            <div>
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                            @if ($activeRole === 'dosen')
                                Mata Kuliah yang Diampu
                            @elseif ($activeRole === 'admin' || $activeRole === 'all')
                                Katalog Mata Kuliah KampusLMS
                            @else
                                Mata Kuliah Semester Ini
                            @endif
                        </h2>
                        <span class="text-xs text-slate-600 font-mono font-semibold">({{ $courses->count() }})</span>
                    </div>

                    <div class="flex items-center gap-3">
                        @if ($activeRole !== 'mahasiswa')
                            <a href="{{ route('courses.create') }}" class="text-xs font-semibold text-slate-700 hover:text-slate-950 transition-colors inline-flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                <span>Tambah MK</span>
                            </a>
                            <span class="text-slate-300" aria-hidden="true">•</span>
                        @endif
                        <a href="{{ route('courses.index') }}" class="text-xs font-semibold text-slate-700 hover:text-slate-950 transition-colors inline-flex items-center gap-1">
                            <span>Lihat Semua</span>
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                    </div>
                </div>

                @if($courses->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach($courses as $course)
                            @php
                                $studentCount = $course->students ? $course->students->count() : 0;
                                $materialCount = $course->materials ? $course->materials->count() : 0;
                                $assignmentCount = $course->assignments ? $course->assignments->count() : 0;
                                $progressPercent = min(100, max(25, ($materialCount * 25) + ($assignmentCount * 15)));
                            @endphp

                            {{-- Clean Minimalist Course Bento Card --}}
                            <a href="{{ route('courses.show', $course) }}" class="lms-card lms-card-hover p-5 flex flex-col justify-between group block" aria-label="Mata kuliah {{ $course->name }}, {{ $course->code }}">
                                <div>
                                    <div class="flex items-center justify-between gap-2 mb-2">
                                        <span class="font-mono text-[11px] font-bold text-slate-800 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">
                                            {{ $course->code }}
                                        </span>
                                        <span class="font-mono text-xs text-slate-600 font-medium">
                                            {{ $course->sks }} SKS
                                        </span>
                                    </div>

                                    <h3 class="font-bold text-sm text-slate-900 group-hover:text-slate-700 transition-colors line-clamp-1 mb-1">
                                        {{ $course->name }}
                                    </h3>

                                    <p class="text-xs text-slate-600 mb-4 font-normal">
                                        {{ $course->lecturer->name ?? 'Dosen Belum Ditugaskan' }}
                                    </p>
                                </div>

                                {{-- Progress Divider --}}
                                <div class="pt-3 border-t border-slate-100">
                                    <div class="flex items-center justify-between text-xs text-slate-600 mb-1.5 font-mono font-medium">
                                        <span>{{ $materialCount }} materi • {{ $assignmentCount }} tugas</span>
                                        <span class="text-slate-900 font-bold">{{ $progressPercent }}%</span>
                                    </div>
                                    <div class="progress-bar" role="progressbar" aria-valuenow="{{ $progressPercent }}" aria-valuemin="0" aria-valuemax="100" aria-label="Kemajuan materi dan tugas {{ $course->name }}">
                                        <div class="progress-bar-fill" style="width: {{ $progressPercent }}%"></div>
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

            {{-- SECTION: 2 Kolom Adaptif (Tugas Perkuliahan & Modul Sekunder) --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                
                {{-- Modul 1: Tugas Perkuliahan --}}
                <div class="lms-card p-5">
                    <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                        <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                            @if ($activeRole === 'dosen')
                                Penugasan yang Sedang Berjalan
                            @elseif ($activeRole === 'admin')
                                Log Penugasan Sistem
                            @else
                                Tugas & Evaluasi Perkuliahan
                            @endif
                        </h2>
                        <span class="text-xs font-mono text-slate-600 font-medium">{{ $homeworks->count() }} Tugas</span>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @forelse($homeworks as $hw)
                            @php
                                $isDuePassed = $hw->due_at && \Carbon\Carbon::parse($hw->due_at)->isPast();
                            @endphp
                            <div class="py-2.5 flex items-center justify-between gap-3 group">
                                <div class="min-w-0">
                                    <h3 class="text-xs font-bold text-slate-900 group-hover:text-slate-700 transition-colors truncate">
                                        {{ $hw->title }}
                                    </h3>
                                    <p class="text-xs text-slate-600 mt-0.5">
                                        <span class="text-slate-800 font-medium">{{ $hw->course->name ?? 'Mata Kuliah' }}</span>
                                        <span aria-hidden="true">•</span>
                                        <span>{{ $hw->due_at ? \Carbon\Carbon::parse($hw->due_at)->format('d M, H:i') : 'Fleksibel' }}</span>
                                    </p>
                                </div>

                                <div class="flex-shrink-0">
                                    @if($isDuePassed)
                                        <span class="badge-coral">Lewat Tenggat</span>
                                    @else
                                        <span class="text-slate-600 text-xs font-mono font-medium">Tersedia</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="py-4 text-center text-slate-600 text-xs">
                                Tidak ada penugasan aktif saat ini.
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Modul 2: Peringkat Nilai Mahasiswa ATAU Daftar Pengguna Terbaru (Role Tailored) --}}
                @if ($activeRole === 'admin')
                    <div class="lms-card p-5">
                        <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                            <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Pengguna Terdaftar</h2>
                            <a href="{{ route('users.index') }}" class="text-xs font-semibold text-slate-700 hover:text-slate-950">
                                Kelola Direktori
                            </a>
                        </div>

                        <div class="divide-y divide-slate-100 text-xs">
                            @forelse($recentUsers as $u)
                                <div class="py-2 flex items-center justify-between">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="w-6 h-6 rounded bg-slate-100 font-bold text-[10px] text-slate-700 flex items-center justify-center font-mono flex-shrink-0">
                                            {{ strtoupper(substr($u->name, 0, 2)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <span class="font-semibold text-slate-900 block truncate">{{ $u->name }}</span>
                                            <span class="text-[11px] text-slate-500 font-mono truncate">{{ $u->email }}</span>
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        @if($u->role === 'dosen')
                                            <span class="badge-sky text-[10px]">Dosen</span>
                                        @elseif($u->role === 'admin')
                                            <span class="badge-coral text-[10px]">Admin</span>
                                        @else
                                            <span class="badge-mint text-[10px]">Mahasiswa</span>
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
                @else
                    <div class="lms-card p-5">
                        <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                            <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                                {{ $activeRole === 'dosen' ? 'Performa Mahasiswa' : 'Peringkat Mahasiswa' }}
                            </h2>
                            <span class="text-xs font-mono text-slate-600 font-medium">Top 4</span>
                        </div>

                        <div class="divide-y divide-slate-100 text-xs">
                            @forelse($topStudents as $idx => $student)
                                <div class="py-2 flex items-center justify-between">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <span class="font-mono text-slate-600 font-bold text-xs w-4 text-center" aria-label="Peringkat {{ $idx + 1 }}">
                                            {{ $idx + 1 }}
                                        </span>
                                        <div class="min-w-0">
                                            <span class="font-semibold text-slate-900 block truncate">{{ $student->name }}</span>
                                            <span class="text-xs text-slate-600 font-mono">{{ $student->nim_nip ?? '102410XX' }}</span>
                                        </div>
                                    </div>
                                    <div class="text-right flex-shrink-0 font-mono font-bold text-slate-900">
                                        {{ $student->calculated_score }} pts
                                    </div>
                                </div>
                            @empty
                                <div class="py-4 text-center text-slate-600 text-xs">
                                    Belum ada data nilai mahasiswa.
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endif

            </div>

        </div>

        {{-- ================================================================
             AREA KANAN: Right Context Panel (Calm & Concise)
             ================================================================ --}}
        <div class="w-full xl:w-72 flex-shrink-0 space-y-5">
            
            {{-- MODUL 1: Jadwal & Deadline Terdekat --}}
            <div class="lms-card p-5">
                <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                    <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Jadwal Mendatang</h2>
                    <span class="text-xs font-mono text-slate-600 font-medium">{{ date('d M Y') }}</span>
                </div>

                <div class="space-y-3">
                    @forelse($upcomingSchedule as $item)
                        <div class="flex items-start gap-2.5 text-xs">
                            <div class="w-5 h-5 rounded bg-slate-100 text-slate-700 flex items-center justify-center flex-shrink-0 mt-0.5" aria-hidden="true">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-slate-900 truncate">{{ $item->title }}</p>
                                <p class="text-xs text-slate-600 font-mono">{{ $item->course->code ?? 'MK' }} • {{ \Carbon\Carbon::parse($item->due_at)->format('H:i, d M') }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="py-2 text-center text-slate-600 text-xs">
                            Tidak ada jadwal mendesak.
                        </div>
                    @endforelse
                </div>

                @if($urgentAssignment)
                    <div class="mt-4 p-3 rounded-lg bg-slate-900 text-white text-xs">
                        <p class="text-[11px] font-mono text-amber-300 font-semibold uppercase tracking-wide">Tenggat Terdekat</p>
                        <p class="font-semibold text-white truncate mt-0.5">{{ $urgentAssignment->title }}</p>
                        <p class="text-xs text-slate-300 font-mono mt-0.5">
                            {{ \Carbon\Carbon::parse($urgentAssignment->due_at)->diffForHumans() }}
                        </p>
                    </div>
                @endif
            </div>

            {{-- MODUL 2: Ringkasan Sistem (Hanya tampil di role Dosen, Admin, & Mode Evaluasi) --}}
            @if ($activeRole !== 'mahasiswa')
                <div class="lms-card p-5 text-xs">
                    <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                        <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Ringkasan Sistem</h2>
                    </div>

                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-600">Materi Terbit</span>
                            <span class="font-mono font-bold text-slate-900">{{ $totalMaterials }} Modul</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-600">Tugas Terbit</span>
                            <span class="font-mono font-bold text-slate-900">{{ $totalAssignments }} Tugas</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-600">Mahasiswa</span>
                            <span class="font-mono font-bold text-slate-900">{{ $totalStudents }} Akun</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-600">Dosen Pengampu</span>
                            <span class="font-mono font-bold text-slate-900">{{ $totalLecturers }} Orang</span>
                        </div>
                    </div>
                </div>
            @endif

        </div>

    </div>

</x-layout>