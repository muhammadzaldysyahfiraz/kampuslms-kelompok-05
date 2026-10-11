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

            {{-- Editorial Greeting & Fast Action Bar (Role Tailored) --}}
            <div class="lms-card p-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-5 border-b border-slate-100">
                    <div>
                        <h2 class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight mt-0.5">
                            @if ($activeRole === 'dosen')
                                Selamat datang, {{ $currentUser->name ?? 'Bapak/Ibu Dosen' }}
                            @elseif ($activeRole === 'admin')
                                Selamat datang, Administrator {{ $currentUser->name ?? '' }}
                            @else
                                Selamat datang, {{ $currentUser->name ?? 'Mahasiswa' }}
                            @endif
                        </h2>
                        <p class="text-xs text-slate-600 mt-1 max-w-xl leading-relaxed">
                            @if ($activeRole === 'dosen')
                                Kelola silabus materi perkuliahan, terbitkan penugasan baru, dan pantau pengumpulan tugas mahasiswa secara terpadu.
                            @elseif ($activeRole === 'admin')
                                Pantau master data sistem KampusLMS, kelola akun pengguna, dan administrasi kurikulum program studi.
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

                {{-- Satu sumber kebenaran metrik: baris KPI tunggal per peran --}}
                <div class="grid grid-cols-2 {{ $activeRole === 'dosen' ? 'sm:grid-cols-5' : 'sm:grid-cols-4' }} gap-4 pt-4 text-xs">
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
                        <div>
                            <span class="text-slate-600 block font-medium text-xs">Modul Materi</span>
                            <span class="text-base font-bold font-mono text-slate-900 mt-0.5 block">{{ $totalMaterials }} Materi</span>
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
                            <span class="text-slate-600 block font-medium text-xs">Tugas Terbit</span>
                            <span class="text-base font-bold font-mono text-slate-900 mt-0.5 block">{{ $totalAssignments }} Tugas</span>
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
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <h2 class="section-title">
                            @if ($activeRole === 'dosen')
                                Mata kuliah yang diampu
                            @elseif ($activeRole === 'admin')
                                Katalog mata kuliah KampusLMS
                            @else
                                Mata kuliah semester ini
                            @endif
                        </h2>
                    </div>

                    <div class="flex items-center gap-4">
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
                                $materialCount = $course->materials ? $course->materials->count() : 0;
                                $assignmentCount = $course->assignments ? $course->assignments->count() : 0;
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

                                {{-- Footer: Jumlah materi & tugas sebenarnya (tanpa metrik dekoratif) --}}
                                <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-3 text-xs">
                                    <span class="text-slate-600 font-mono">
                                        {{ $materialCount }} materi <span aria-hidden="true">·</span> {{ $assignmentCount }} tugas
                                    </span>
                                    @if ($course->status === 'active')
                                        <span class="status status-active"><span class="status-dot" aria-hidden="true"></span>Aktif</span>
                                    @elseif ($course->status === 'draft')
                                        <span class="status status-draft"><span class="status-dot" aria-hidden="true"></span>Draft</span>
                                    @else
                                        <span class="status status-archived"><span class="status-dot" aria-hidden="true"></span>Diarsipkan</span>
                                    @endif
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
            <div class="grid grid-cols-1 {{ $activeRole === 'mahasiswa' ? '' : 'lg:grid-cols-2' }} gap-5">
                
                {{-- Modul 1: Actionable Task Module (Role-Tailored: Mahasiswa / Dosen / Admin) --}}
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
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <h3 class="text-xs font-bold text-slate-900 group-hover:text-slate-700 transition-colors truncate">
                                                {{ $sub->student->name ?? 'Mahasiswa' }}
                                            </h3>
                                            @if($sub->is_late)
                                                <span class="text-[10px] font-mono text-rose-600 bg-rose-50 px-1.5 py-0.2 rounded border border-rose-200">Terlambat</span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-slate-600 mt-0.5 truncate">
                                            <span class="font-medium text-slate-800">{{ $sub->assignment->title ?? 'Tugas' }}</span>
                                            <span aria-hidden="true">•</span>
                                            <span class="text-slate-500">{{ $sub->assignment->course->code ?? 'MK' }}</span>
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

                    @elseif ($activeRole === 'admin')
                        {{-- ADMIN: Rekap Pengumpulan & Log Penugasan Sistem --}}
                        <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                            <div>
                                <h2 class="section-title">Log aktivitas penugasan sistem</h2>
                                <p class="text-[11px] text-slate-500 mt-0.5">Pantauan progres evaluasi perkuliahan lintas kelas</p>
                            </div>
                            <a href="{{ route('admin.submissions.index') }}" class="text-xs font-semibold text-slate-700 hover:text-slate-950 flex items-center gap-1">
                                Semua log →
                            </a>
                        </div>

                        <div class="divide-y divide-slate-100">
                            @forelse($systemAssignments as $hw)
                                <div class="py-2.5 flex items-center justify-between gap-3 group">
                                    <div class="min-w-0">
                                        <h3 class="text-xs font-bold text-slate-900 group-hover:text-slate-700 transition-colors truncate">
                                            {{ $hw->title }}
                                        </h3>
                                        <p class="text-xs text-slate-600 mt-0.5">
                                            <span class="text-slate-800 font-medium">{{ $hw->course->name ?? 'Mata Kuliah' }}</span>
                                            <span aria-hidden="true">•</span>
                                            <span class="font-mono text-slate-500">{{ $hw->submissions_count }} mahasiswa mengumpulkan</span>
                                        </p>
                                    </div>

                                    <div class="flex-shrink-0">
                                        <a href="{{ route('assignments.show', $hw->id) }}" class="text-[11px] font-semibold text-slate-700 hover:text-slate-950 px-2.5 py-1 bg-slate-50 hover:bg-slate-100 rounded border border-slate-200 transition-colors">
                                            Detail
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="py-6 text-center text-slate-500 text-xs">
                                    Tidak ada log penugasan sistem.
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
                                    <div class="min-w-0">
                                        <h3 class="text-xs font-bold text-slate-900 group-hover:text-slate-700 transition-colors truncate">
                                            {{ $hw->title }}
                                        </h3>
                                        <p class="text-xs text-slate-600 mt-0.5">
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
                                            <a href="{{ route('submissions.show', $userSub->id) }}" class="text-[11px] font-semibold text-slate-700 hover:text-slate-950 px-2 py-1 bg-slate-50 hover:bg-slate-100 rounded border border-slate-200 transition-colors">
                                                Bukti
                                            </a>
                                        @else
                                            <a href="{{ route('assignments.show', $hw->id) }}" class="text-[11px] font-semibold text-emerald-700 hover:text-emerald-900 px-2 py-1 bg-emerald-50 hover:bg-emerald-100 rounded border border-emerald-200 transition-colors">
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

                {{-- Modul 2: Peringkat Nilai Mahasiswa ATAU Daftar Pengguna Terbaru (Role Tailored) --}}
                @if ($activeRole === 'admin')
                    <div class="lms-card p-5">
                        <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                            <h2 class="section-title">Pengguna terdaftar</h2>
                            <a href="{{ route('users.index') }}" class="text-xs font-semibold text-slate-700 hover:text-slate-950">
                                Kelola direktori
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
                                            <span class="status status-dosen"><span class="status-dot" aria-hidden="true"></span>Dosen</span>
                                        @elseif($u->role === 'admin')
                                            <span class="status status-admin"><span class="status-dot" aria-hidden="true"></span>Admin</span>
                                        @else
                                            <span class="status status-mahasiswa"><span class="status-dot" aria-hidden="true"></span>Mahasiswa</span>
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
                @elseif ($activeRole === 'dosen')
                    <div class="lms-card p-5">
                        <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                            <h2 class="section-title">Performa mahasiswa</h2>
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
                                            <span class="text-xs text-slate-600 font-mono">{{ $student->nim_nip ?? '—' }}</span>
                                        </div>
                                    </div>
                                    <div class="text-right flex-shrink-0 font-mono font-bold text-slate-900">
                                        {{ $student->submissions_count }} pengumpulan
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
                    <div>
                        <h2 class="section-title">Jadwal mendatang</h2>
                    </div>
                    <span class="text-xs font-mono text-slate-600 font-medium">{{ date('d M Y') }}</span>
                </div>

                <div class="space-y-3">
                    @forelse($upcomingSchedule as $item)
                        <div class="flex items-start gap-2.5 text-xs group">
                            <div class="w-5 h-5 rounded bg-slate-100 text-slate-700 flex items-center justify-center flex-shrink-0 mt-0.5" aria-hidden="true">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('assignments.show', $item->id) }}" class="font-semibold text-slate-900 group-hover:text-slate-700 truncate block transition-colors">
                                    {{ $item->title }}
                                </a>
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
                        <div class="flex items-center justify-between">
                            <p class="text-[11px] font-mono text-amber-300 font-semibold">Tenggat terdekat</p>
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse" aria-hidden="true"></span>
                        </div>
                        <p class="font-semibold text-white truncate mt-1">{{ $urgentAssignment->title }}</p>
                        <p class="text-xs text-slate-300 font-mono mt-0.5">
                            {{ \Carbon\Carbon::parse($urgentAssignment->due_at)->locale('id')->diffForHumans() }}
                        </p>
                        <div class="mt-2.5 pt-2 border-t border-slate-800">
                            <a href="{{ route('assignments.show', $urgentAssignment->id) }}" class="text-[11px] text-amber-300 hover:text-white font-medium flex items-center gap-1 transition-colors">
                                Buka rincian tugas →
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            {{-- MODUL 2: Catatan bahwa ringkasan angka hanya muncul sekali di KPI utama
                 (Axiom 3: satu sumber kebenaran metrik per viewport). --}}

        </div>

    </div>

</x-layout>