<x-layout title="Dashboard">

    {{-- ====================================================================
         DASHBOARD 3-PANEL LAYOUT (Aesthetic dari DESIGN_REFERENCE.pdf image_013)
         Konten 100% Dinamis dari Database KampusLMS (Pemrograman Web SI ITK)
         ==================================================================== --}}
    <div class="flex flex-col xl:flex-row gap-8 items-start">
        
        {{-- ================================================================
             AREA TENGAH: Dashboard Main Content
             ================================================================ --}}
        <div class="flex-1 min-w-0 w-full space-y-7">
            
            {{-- Header: Judul & Search Bar Akademik --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        Dashboard Perkuliahan
                    </h1>
                    <p class="text-xs text-slate-500 mt-1">
                        Selamat datang di portal akademik <strong>KampusLMS</strong> • Semester Ganjil 2026/2027
                    </p>
                </div>

                {{-- Search Bar dengan Style Referensi (Kaca Pembesar + Mic) --}}
                <div class="relative w-full sm:w-96">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        placeholder="Cari mata kuliah, materi, atau tugas..." 
                        class="w-full pl-10 pr-10 py-2.5 bg-white rounded-xl border border-slate-200 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#66A7F2] shadow-xs"
                    >
                    <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 cursor-pointer hover:text-slate-600" title="Voice Search">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 0 0 6-6v-1.5m-6 7.5a6 6 0 0 1-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 0 1-3-3V4.5a3 3 0 1 1 6 0v8.25a3 3 0 0 1-3 3Z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- SECTION: My courses (Data Riil dari Database KampusLMS) --}}
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Mata Kuliah Semester Ini</h2>
                    <a href="{{ route('courses.index') }}" class="btn-outline text-xs py-1.5 px-3">
                        Lihat Semua
                    </a>
                </div>

                @if($courses->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        @php
                            $accents = [
                                ['color' => '#FFC152', 'bg' => 'bg-[#FFC152]', 'text' => 'text-[#FFC152]'],
                                ['color' => '#5DD299', 'bg' => 'bg-[#5DD299]', 'text' => 'text-[#5DD299]'],
                                ['color' => '#FE774C', 'bg' => 'bg-[#FE774C]', 'text' => 'text-[#FE774C]'],
                            ];
                        @endphp

                        @foreach($courses as $index => $course)
                            @php
                                $accent = $accents[$index % 3];
                                $studentCount = $course->students ? $course->students->count() : 0;
                                $materialCount = $course->materials ? $course->materials->count() : 0;
                                $assignmentCount = $course->assignments ? $course->assignments->count() : 0;
                                $progressPercent = min(100, max(20, ($materialCount * 25) + ($assignmentCount * 15)));
                            @endphp

                            {{-- Course Card Bento Style --}}
                            <div class="lms-card p-5 relative overflow-hidden flex flex-col justify-between hover:shadow-md transition-shadow group">
                                <div>
                                    <div class="flex items-start justify-between mb-2">
                                        <div class="min-w-0 pr-2">
                                            <a href="{{ route('courses.show', $course) }}" class="font-extrabold text-base text-slate-900 hover:text-[#FE774C] transition-colors truncate block">
                                                {{ $course->name }}
                                            </a>
                                            <p class="text-[11px] text-slate-400 mt-0.5 font-mono">
                                                {{ $course->code }} • {{ $course->sks }} SKS
                                            </p>
                                        </div>
                                        <div class="{{ $accent['text'] }} flex-shrink-0">
                                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                                <path d="M5 4a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v18l-7-3.5L5 22V4z"/>
                                            </svg>
                                        </div>
                                    </div>

                                    {{-- Info Dosen & Row Avatars Mahasiswa Terdaftar --}}
                                    <div class="my-4">
                                        <p class="text-xs text-slate-600 font-semibold mb-2">
                                            Dosen: <span class="text-slate-900 font-bold">{{ $course->lecturer->name ?? 'Belum ditentukan' }}</span>
                                        </p>
                                        <div class="flex items-center justify-between">
                                            <div class="flex -space-x-2 overflow-hidden items-center">
                                                @forelse($course->students->take(3) as $st)
                                                    <div class="inline-flex items-center justify-center h-7 w-7 rounded-full ring-2 ring-white bg-slate-200 text-slate-700 text-[10px] font-bold" title="{{ $st->name }}">
                                                        {{ strtoupper(substr($st->name, 0, 2)) }}
                                                    </div>
                                                @empty
                                                    <span class="text-[11px] text-slate-400">Belum ada mahasiswa</span>
                                                @endforelse

                                                @if($studentCount > 3)
                                                    <span class="inline-flex items-center justify-center h-7 w-7 rounded-full ring-2 ring-white bg-[#1E1E26] text-white text-[9px] font-bold">
                                                        +{{ $studentCount - 3 }}
                                                    </span>
                                                @endif
                                            </div>

                                            <a href="{{ route('courses.show', $course) }}" class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 group-hover:bg-[#1E1E26] group-hover:text-white flex items-center justify-center text-[10px] transition-colors">
                                                →
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                {{-- Progress Modul & Persentase Sesuai Referensi --}}
                                <div class="pt-3 border-t border-slate-100">
                                    <div class="flex items-center justify-between text-[11px] font-bold text-slate-500 mb-1.5">
                                        <span>{{ $materialCount }} materi • {{ $assignmentCount }} tugas</span>
                                        <span class="text-slate-900">{{ $progressPercent }}%</span>
                                    </div>
                                    <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full {{ $accent['bg'] }} rounded-full transition-all duration-500" style="width: {{ $progressPercent }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="lms-card p-8 text-center">
                        <p class="text-sm text-slate-500 font-semibold mb-3">Belum ada mata kuliah yang terdaftar di database.</p>
                        <a href="{{ route('courses.create') }}" class="btn-primary text-xs py-2 px-4">
                            + Tambah Mata Kuliah Pertama
                        </a>
                    </div>
                @endif
            </div>

            {{-- SECTION: Baris Tengah (Rating Mahasiswa & Grafik Nilai Akademik) --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                {{-- WIDGET 1: Rating (Leaderboard Mahasiswa Riil dari Database) --}}
                <div class="lms-card p-5 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <h2 class="text-base font-extrabold text-slate-900">Peringkat Kelas</h2>
                                <span class="text-xs text-slate-500 bg-slate-100 px-2 py-0.5 rounded font-semibold">
                                    {{ $courses->first()->name ?? 'Pemrograman Web' }} ▾
                                </span>
                            </div>
                            <a href="{{ route('users.index') }}" class="btn-outline text-xs py-1 px-2.5">
                                Semua Mahasiswa
                            </a>
                        </div>

                        {{-- Strip Metrik: Peringkat, Skor Rata-rata, Tugas Selesai --}}
                        <div class="flex items-center justify-around py-3 px-4 rounded-xl bg-slate-50 border border-slate-100 mb-4">
                            <div class="text-center">
                                <div class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">
                                    <span>Top 5%</span>
                                    <span>↑</span>
                                </div>
                                <p class="text-[10px] text-slate-400 mt-1 font-semibold uppercase">Posisi</p>
                            </div>

                            <div class="text-center">
                                <div class="w-10 h-10 rounded-full bg-[#66A7F2] text-white flex items-center justify-center font-extrabold text-xs shadow-xs mx-auto font-mono">
                                    88
                                </div>
                                <p class="text-[10px] text-slate-400 mt-1 font-semibold uppercase">Avg Score</p>
                            </div>

                            <div class="text-center">
                                <div class="inline-flex items-center gap-1 text-xs font-bold text-slate-800 bg-slate-200/70 px-2 py-0.5 rounded-full font-mono">
                                    {{ $totalSubmissions }}
                                </div>
                                <p class="text-[10px] text-slate-400 mt-1 font-semibold uppercase">Submissions</p>
                            </div>
                        </div>

                        {{-- Tabel Leaderboard Mahasiswa Riil --}}
                        <div class="space-y-2.5 text-xs">
                            @forelse($topStudents as $idx => $student)
                                @php
                                    $medals = ['bg-amber-100 text-amber-800', 'bg-slate-200 text-slate-700', 'bg-orange-100 text-orange-800', 'bg-slate-100 text-slate-600'];
                                @endphp
                                <div class="flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <span class="w-5 h-5 rounded-full {{ $medals[$idx] ?? 'bg-slate-100 text-slate-500' }} flex items-center justify-center font-bold text-[10px]">
                                            {{ $idx + 1 }}
                                        </span>
                                        <div class="w-7 h-7 rounded-full bg-slate-200 text-slate-700 font-bold text-xs flex items-center justify-center">
                                            {{ strtoupper(substr($student->name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <span class="font-bold text-slate-900 block truncate">{{ $student->name }}</span>
                                            <span class="text-[10px] text-slate-400 font-mono">{{ $student->nim_nip ?? 'Mahasiswa ITK' }}</span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="font-mono text-slate-900 font-bold text-xs">{{ $student->calculated_score }} pts</span>
                                        <span class="text-[10px] text-slate-400 block font-mono">{{ $student->submissions_count }} tugas</span>
                                    </div>
                                </div>
                            @empty
                                <div class="p-3 text-center text-slate-400 text-xs">
                                    Belum ada data nilai mahasiswa.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- WIDGET 2: My scores (Grafik Perkembangan Nilai Akademik) --}}
                <div class="lms-card p-5 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <h2 class="text-base font-extrabold text-slate-900">Perkembangan Nilai</h2>
                            <span class="text-xs text-slate-500 bg-slate-100 px-2 py-0.5 rounded font-semibold">
                                Tugas 1 - 7 ▾
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-400">Distribusi nilai evaluasi tugas mahasiswa semester berjalan</p>

                        {{-- Area Chart SVG (Sesuai grafik kuning di image_013.jpg) --}}
                        <div class="relative mt-4">
                            
                            {{-- Tooltip Data Point Popup --}}
                            <div class="absolute left-24 top-2 bg-white px-2.5 py-1.5 rounded-lg shadow-md border border-slate-200 text-[10px] z-10">
                                <p class="text-slate-500">Nilai Pertama: <strong class="text-slate-900 font-bold">75</strong></p>
                                <p class="text-slate-500">Evaluasi Akhir: <strong class="text-[#FE774C] font-bold">92</strong></p>
                            </div>

                            {{-- SVG Chart --}}
                            <svg class="w-full h-44 overflow-visible" viewBox="0 0 350 140" fill="none">
                                <defs>
                                    <linearGradient id="scoreAmberGradient" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stop-color="#FFC152" stop-opacity="0.85"/>
                                        <stop offset="100%" stop-color="#FFC152" stop-opacity="0.1"/>
                                    </linearGradient>
                                </defs>

                                {{-- Grid horizontal lines --}}
                                <line x1="25" y1="20" x2="340" y2="20" stroke="#F1F5F9" stroke-width="1"/>
                                <line x1="25" y1="50" x2="340" y2="50" stroke="#F1F5F9" stroke-width="1"/>
                                <line x1="25" y1="80" x2="340" y2="80" stroke="#F1F5F9" stroke-width="1"/>
                                <line x1="25" y1="110" x2="340" y2="110" stroke="#F1F5F9" stroke-width="1"/>

                                {{-- Y Axis Labels --}}
                                <text x="5" y="24" class="text-[9px] fill-slate-400 font-mono">100</text>
                                <text x="5" y="54" class="text-[9px] fill-slate-400 font-mono">80</text>
                                <text x="5" y="84" class="text-[9px] fill-slate-400 font-mono">60</text>
                                <text x="5" y="114" class="text-[9px] fill-slate-400 font-mono">40</text>

                                {{-- Area Path Curve --}}
                                <path 
                                    d="M 30 110 Q 70 95 110 50 T 180 45 T 250 60 T 330 35 L 330 120 L 30 120 Z" 
                                    fill="url(#scoreAmberGradient)"
                                />
                                
                                {{-- Stroke Curve --}}
                                <path 
                                    d="M 30 110 Q 70 95 110 50 T 180 45 T 250 60 T 330 35" 
                                    stroke="#FFC152" 
                                    stroke-width="2.5" 
                                    stroke-linecap="round"
                                />

                                {{-- Vertical Dashed Guide line for tooltip --}}
                                <line x1="110" y1="45" x2="110" y2="120" stroke="#94A3B8" stroke-width="1" stroke-dasharray="3 3"/>
                                <circle cx="110" cy="50" r="4" fill="#ffffff" stroke="#FFC152" stroke-width="3"/>
                            </svg>

                            {{-- X Axis Labels (Tugas Akademik) --}}
                            <div class="flex justify-between pl-6 pr-2 text-[10px] font-mono text-slate-400 mt-1">
                                <span>TGS 1</span>
                                <span>TGS 2</span>
                                <span>TGS 3</span>
                                <span>UTS</span>
                                <span>TGS 4</span>
                                <span>TGS 5</span>
                                <span>UAS</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- SECTION: Homeworks (Daftar Tugas Akademik Riil dari Database) --}}
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Tugas Perkuliahan</h2>
                    <span class="text-xs text-slate-400">Semester Ganjil 2026/2027</span>
                </div>

                <div class="space-y-3">
                    @forelse($homeworks as $idx => $hw)
                        @php
                            $isDuePassed = $hw->due_at && \Carbon\Carbon::parse($hw->due_at)->isPast();
                            $colors = ['bg-indigo-50 border-indigo-200 text-indigo-700', 'bg-amber-50 border-amber-200 text-amber-700', 'bg-emerald-50 border-emerald-200 text-emerald-700'];
                        @endphp
                        <div class="lms-card p-4 flex items-center justify-between hover:border-slate-300 transition-colors cursor-pointer group">
                            <div class="flex items-center gap-3.5">
                                <div class="w-10 h-10 rounded-xl border flex items-center justify-center font-extrabold text-xs font-mono {{ $colors[$idx % 3] }}">
                                    {{ strtoupper(substr($hw->course->code ?? 'MK', 0, 2)) }}
                                </div>
                                <div>
                                    <h3 class="text-xs sm:text-sm font-extrabold text-slate-900 group-hover:text-[#FE774C] transition-colors">
                                        {{ $hw->title }}
                                    </h3>
                                    <div class="flex items-center gap-2 text-[11px] text-slate-400 mt-0.5">
                                        <span class="font-semibold text-slate-600">{{ $hw->course->name ?? 'Mata Kuliah' }}</span>
                                        <span>•</span>
                                        <span>Tenggat: {{ $hw->due_at ? \Carbon\Carbon::parse($hw->due_at)->format('d M Y, H:i') : 'Fleksibel' }}</span>
                                        <span>•</span>
                                        <span class="flex items-center gap-1">
                                            <span>Bobot:</span>
                                            <span class="inline-flex gap-0.5">
                                                <span class="w-3 h-1.5 rounded-full bg-[#5DD299]"></span>
                                                <span class="w-3 h-1.5 rounded-full {{ $idx > 0 ? 'bg-[#5DD299]' : 'bg-slate-200' }}"></span>
                                                <span class="w-3 h-1.5 rounded-full {{ $idx > 1 ? 'bg-[#FE774C]' : 'bg-slate-200' }}"></span>
                                            </span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                @if($isDuePassed)
                                    <span class="badge-coral text-[10px] px-2 py-0.5 hidden sm:inline-block">Lewat Deadline</span>
                                @else
                                    <span class="badge-mint text-[10px] px-2 py-0.5 hidden sm:inline-block">Aktif</span>
                                @endif
                                <span class="text-slate-400 group-hover:text-slate-700 transition-colors">
                                    →
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="lms-card p-6 text-center text-slate-400 text-xs">
                            Belum ada penugasan yang aktif saat ini.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- ================================================================
             AREA KANAN: Right Panel (Profil, Streak, Kalender, Progress, Schedule)
             Sesuai panel kanan image_013.jpg
             ================================================================ --}}
        <div class="w-full xl:w-80 flex-shrink-0 space-y-6">
            
            {{-- 1. Profil Pengguna Riil dari Database --}}
            <div class="lms-card p-5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-tr from-[#66A7F2] to-[#5DD299] flex items-center justify-center text-white font-extrabold text-sm ring-2 ring-slate-100 shadow-xs">
                        {{ strtoupper(substr($currentUser->name ?? 'MD', 0, 2)) }}
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-extrabold text-slate-900 truncate">{{ $currentUser->name ?? 'Mahasiswa Demo' }}</h3>
                        <p class="text-[11px] text-slate-400 truncate font-mono">{{ $currentUser->email ?? 'mahasiswa@kampuslms.test' }}</p>
                        <span class="inline-block mt-0.5 text-[9px] font-bold uppercase tracking-wider text-[#FE774C] bg-[#FE774C]/10 px-1.5 py-0.5 rounded">
                            {{ $currentUser->role ?? 'mahasiswa' }} • {{ $currentUser->nim_nip ?? '10241050' }}
                        </span>
                    </div>
                </div>
                <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center text-xs font-bold" title="Akun Aktif">
                    ✓
                </div>
            </div>

            {{-- 2. Streak Counter (Flame Gauge Keaktifan Belajar) --}}
            <div class="lms-card p-5 text-center">
                <div class="relative w-36 h-20 mx-auto overflow-hidden">
                    {{-- Semi circle rainbow gauge --}}
                    <svg class="w-36 h-36 -rotate-90 origin-center" viewBox="0 0 100 100">
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#F1F5F9" stroke-width="8"/>
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#5DD299" stroke-width="8" stroke-dasharray="125 250" stroke-linecap="round"/>
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#FFC152" stroke-width="8" stroke-dasharray="70 250" stroke-linecap="round"/>
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#FE774C" stroke-width="8" stroke-dasharray="30 250" stroke-linecap="round"/>
                    </svg>
                    {{-- Center Flame --}}
                    <div class="absolute inset-x-0 bottom-0 flex flex-col items-center justify-center">
                        <span class="text-2xl">🔥</span>
                    </div>
                </div>
                <div class="mt-1">
                    <span class="text-xl font-extrabold text-slate-900">{{ $totalSubmissions }}</span>
                    <p class="text-[11px] text-slate-400 font-semibold">Tugas terkumpul semester ini</p>
                    <span class="text-[10px] text-emerald-600 font-bold bg-emerald-50 px-2 py-0.5 rounded-full inline-block mt-1">
                        +12% keaktifan akademik
                    </span>
                </div>
            </div>

            {{-- 3. Mini Calendar Akademik (Bulan Berjalan) --}}
            @php
                $currentMonthName = date('F Y');
                $todayDate = date('j');
                $todayDay = date('D');
            @endphp
            <div class="lms-card p-5">
                <div class="flex items-center justify-between mb-3.5 text-xs font-bold text-slate-800">
                    <span class="text-slate-400">‹</span>
                    <span>{{ $currentMonthName }}</span>
                    <span class="text-slate-400">›</span>
                </div>

                <div class="grid grid-cols-6 gap-1 text-center">
                    @for($d = 1; $d <= 6; $d++)
                        @php
                            $dayNumber = max(1, $todayDate - 3 + $d);
                            $isCurrent = ($d == 4);
                        @endphp
                        @if($isCurrent)
                            {{-- Hari Aktif (Mint Aksen) --}}
                            <div class="p-1.5 rounded-xl bg-[#5DD299] text-white text-[10px] shadow-sm">
                                <span>{{ $todayDay }}</span>
                                <span class="block font-extrabold text-white text-xs mt-0.5">{{ $todayDate }}</span>
                            </div>
                        @else
                            <div class="p-1.5 rounded-lg text-slate-400 text-[10px]">
                                <span>{{ ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'][$d - 1] }}</span>
                                <span class="block font-bold text-slate-700 text-xs mt-0.5">{{ $dayNumber }}</span>
                            </div>
                        @endif
                    @endfor
                </div>
            </div>

            {{-- 4. Courses Progress (4-Box Bento Grid Statistik KampusLMS) --}}
            <div class="lms-card p-5">
                <h3 class="text-xs font-extrabold text-slate-800 mb-3.5">Statistik Akademik</h3>
                
                <div class="grid grid-cols-2 gap-3 text-xs">
                    {{-- Box 1: Materi Kuliah --}}
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="font-extrabold text-slate-900 text-sm">{{ $totalMaterials }}</span>
                        <p class="text-[10px] text-slate-400 mt-0.5">Materi Kuliah</p>
                        <div class="w-full h-1 bg-slate-200 rounded-full mt-2 overflow-hidden">
                            <div class="h-full bg-[#5DD299] rounded-full" style="width: 80%"></div>
                        </div>
                    </div>

                    {{-- Box 2: Tugas Terbit --}}
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="font-extrabold text-slate-900 text-sm">{{ $totalAssignments }}</span>
                        <p class="text-[10px] text-slate-400 mt-0.5">Tugas Terbit</p>
                    </div>

                    {{-- Box 3: Mahasiswa Aktif --}}
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="font-extrabold text-slate-900 text-sm">{{ $totalStudents }}</span>
                        <p class="text-[10px] text-slate-400 mt-0.5">Mahasiswa</p>
                    </div>

                    {{-- Box 4: Dosen Pengampu --}}
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="font-extrabold text-slate-900 text-sm">{{ $totalLecturers }}</span>
                        <p class="text-[10px] text-slate-400 mt-0.5">Dosen Pengampu</p>
                    </div>
                </div>
            </div>

            {{-- 5. Schedule (Jadwal Terdekat Akademik) --}}
            <div class="lms-card p-5">
                <div class="flex items-center justify-between mb-3.5">
                    <h3 class="text-xs font-extrabold text-slate-800">Jadwal Perkuliahan & Tugas</h3>
                    <span class="text-[11px] font-bold text-slate-400">Mendatang</span>
                </div>

                <div class="space-y-2.5">
                    @forelse($upcomingSchedule as $item)
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100 hover:border-slate-200 transition-colors">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-lg bg-sky-50 text-[#66A7F2] flex items-center justify-center font-bold text-xs flex-shrink-0">
                                    📝
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-extrabold text-slate-800 truncate">{{ $item->title }}</p>
                                    <p class="text-[10px] text-slate-400 truncate">{{ $item->course->name ?? 'MK' }} • {{ \Carbon\Carbon::parse($item->due_at)->format('H:i, d M') }}</p>
                                </div>
                            </div>
                            <span class="text-slate-400 text-xs">›</span>
                        </div>
                    @empty
                        <div class="p-3 text-center text-slate-400 text-xs">
                            Tidak ada deadline mendesak hari ini.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- 6. Notification Popup Banner (Dark #1E1E26) --}}
            @if($urgentAssignment)
                <div class="p-4 rounded-2xl bg-[#1E1E26] text-white shadow-lg border border-[#363644] flex items-start justify-between gap-3">
                    <div class="flex items-start gap-3 min-w-0">
                        <div class="w-8 h-8 rounded-xl bg-[#252530] text-[#FFC152] flex items-center justify-center text-sm flex-shrink-0">
                            🔔
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-extrabold text-white truncate">Tenggat Tugas Mendekati!</p>
                            <p class="text-[11px] text-slate-400 mt-0.5 truncate">
                                {{ $urgentAssignment->title }} ({{ $urgentAssignment->course->name }})
                            </p>
                            <span class="text-[10px] text-[#FE774C] font-bold block mt-1">
                                Berakhir {{ \Carbon\Carbon::parse($urgentAssignment->due_at)->diffForHumans() }}
                            </span>
                        </div>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-white text-sm">
                        &times;
                    </button>
                </div>
            @else
                <div class="p-4 rounded-2xl bg-[#1E1E26] text-white shadow-lg border border-[#363644] flex items-start justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl bg-[#252530] text-[#5DD299] flex items-center justify-center text-sm flex-shrink-0">
                            ✦
                        </div>
                        <div>
                            <p class="text-xs font-extrabold text-white">Status Akademik Baik</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">Seluruh tugas perkuliahan telah ditinjau dan terkelola rapi.</p>
                        </div>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-white text-sm">
                        &times;
                    </button>
                </div>
            @endif

        </div>

    </div>

</x-layout>