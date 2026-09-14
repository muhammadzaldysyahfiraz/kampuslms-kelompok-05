<x-layout title="Dashboard">

    {{-- Hero Welcome Banner (DESIGN.md Section 1.2 & 5.1) --}}
    <div class="lms-card p-6 sm:p-8 mb-8 relative overflow-hidden bg-gradient-to-r from-slate-900 via-[#1E1E26] to-[#252530] text-white border-0 shadow-lg">
        
        {{-- Ambient decorative glow --}}
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-[#FE774C]/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute right-32 -top-10 w-64 h-64 bg-[#66A7F2]/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 max-w-2xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs font-semibold text-[#FFC152] backdrop-blur-md mb-4 border border-white/10">
                <span>✦</span>
                <span>Sistem Pembelajaran Terpadu SI2514024</span>
            </div>

            <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight text-white mb-2 leading-tight">
                Selamat Datang di KampusLMS
            </h1>

            <p class="text-sm sm:text-base text-slate-300 mb-6 leading-relaxed">
                Platform kurikulum terpadu berbasis Laravel 12. Kelola data perkuliahan, penugasan dosen, dan materi akademik dengan alur kerja modern dan terstruktur.
            </p>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('courses.index') }}" class="btn-primary text-xs py-2.5 px-4">
                    <span>Jelajahi Mata Kuliah</span>
                    <span>&rarr;</span>
                </a>
                <a href="{{ route('courses.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold transition-all border border-white/15">
                    <span>+ Tambah Mata Kuliah</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Stat Bento Cards Grid (4 Metrik Utama) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-10">
        
        {{-- Card 1: Total Mata Kuliah (Amber) --}}
        <div class="lms-card lms-card-hover p-5 border-l-4 border-l-[#FFC152]">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Mata Kuliah</span>
                <span class="w-8 h-8 rounded-lg bg-amber-50 text-[#FFC152] flex items-center justify-center font-bold text-sm">
                    📚
                </span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-slate-900">{{ $totalCourses }}</span>
                <span class="text-xs text-slate-500">MK Terdaftar</span>
            </div>
            <p class="text-[11px] text-slate-400 mt-2">Termasuk draft & kurikulum aktif</p>
        </div>

        {{-- Card 2: Dosen Pengampu (Mint) --}}
        <div class="lms-card lms-card-hover p-5 border-l-4 border-l-[#5DD299]">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tenaga Pengajar</span>
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-[#5DD299] flex items-center justify-center font-bold text-sm">
                    👨‍🏫
                </span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-slate-900">{{ $totalLecturers }}</span>
                <span class="text-xs text-slate-500">Dosen Aktif</span>
            </div>
            <p class="text-[11px] text-slate-400 mt-2">Diverifikasi di sistem akademik</p>
        </div>

        {{-- Card 3: Mahasiswa Terdaftar (Sky Blue) --}}
        <div class="lms-card lms-card-hover p-5 border-l-4 border-l-[#66A7F2]">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Mahasiswa</span>
                <span class="w-8 h-8 rounded-lg bg-sky-50 text-[#66A7F2] flex items-center justify-center font-bold text-sm">
                    🎓
                </span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-slate-900">{{ $totalStudents }}</span>
                <span class="text-xs text-slate-500">Akun Siswa</span>
            </div>
            <p class="text-[11px] text-slate-400 mt-2">Tersebar di kelas perkuliahan</p>
        </div>

        {{-- Card 4: Tugas & Evaluasi (Coral) --}}
        <div class="lms-card lms-card-hover p-5 border-l-4 border-l-[#FE774C]">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tugas & Evaluasi</span>
                <span class="w-8 h-8 rounded-lg bg-rose-50 text-[#FE774C] flex items-center justify-center font-bold text-sm">
                    📝
                </span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-slate-900">{{ $totalAssignments }}</span>
                <span class="text-xs text-slate-500">Penugasan</span>
            </div>
            <p class="text-[11px] text-slate-400 mt-2">Mendukung evaluasi berkala</p>
        </div>

    </div>

    {{-- Section: Mata Kuliah Unggulan & Quick View --}}
    <div class="mb-10">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 tracking-tight">Mata Kuliah Terbaru</h2>
                <p class="text-xs text-slate-500">Akses cepat ke mata kuliah yang baru diperbarui</p>
            </div>
            <a href="{{ route('courses.index') }}" class="text-xs font-bold text-[#66A7F2] hover:underline inline-flex items-center gap-1">
                <span>Lihat Semua ({{ $totalCourses }})</span>
                <span>&rarr;</span>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            @forelse ($courses as $index => $course)
                @php
                    $accents = ['#FFC152', '#5DD299', '#66A7F2'];
                    $acc = $accents[$index % count($accents)];
                @endphp
                <div class="lms-card lms-card-hover p-5 flex flex-col justify-between relative overflow-hidden">
                    <div class="absolute top-0 left-0 right-0 h-1" style="background-color: {{ $acc }}"></div>
                    
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <span class="font-mono text-[11px] font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-700">
                                {{ $course->code }}
                            </span>
                            <span class="text-[11px] font-extrabold text-slate-500">{{ $course->sks }} SKS</span>
                        </div>
                        
                        <h3 class="text-base font-extrabold text-slate-900 mb-1 hover:text-[#FE774C] transition-colors line-clamp-1">
                            <a href="{{ route('courses.show', $course) }}">
                                {{ $course->name }}
                            </a>
                        </h3>

                        <p class="text-xs text-slate-500 line-clamp-2 mb-4">
                            {{ $course->description ?? 'Tidak ada deskripsi rinci.' }}
                        </p>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-700 truncate max-w-[150px]">
                            {{ $course->lecturer->name ?? 'Dosen Belum Ditugaskan' }}
                        </span>
                        <a href="{{ route('courses.show', $course) }}" class="btn-secondary text-[11px] py-1.5 px-3">
                            Buka
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full lms-card p-8 text-center text-slate-400 text-sm">
                    Belum ada mata kuliah yang terdaftar di database.
                </div>
            @endforelse
        </div>
    </div>

    {{-- Section: Informasi Demo & Milestone M1 Checklist --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        {{-- Card: Akun Demo Wajib Penguji --}}
        <div class="lms-card p-6">
            <div class="flex items-center gap-2.5 mb-4">
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-[#5DD299] flex items-center justify-center font-bold">
                    🔑
                </span>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Akun Pengujian Demo (Wajib M1)</h3>
                    <p class="text-xs text-slate-500">Kredensial bawaan yang telah ter-generate melalui DatabaseSeeder</p>
                </div>
            </div>

            <div class="space-y-2.5 text-xs font-mono">
                <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-200 flex items-center justify-between">
                    <div>
                        <span class="text-slate-400">Admin:</span>
                        <span class="text-slate-800 font-bold ml-1">admin@kampuslms.test</span>
                    </div>
                    <span class="text-slate-500">password: password</span>
                </div>
                <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-200 flex items-center justify-between">
                    <div>
                        <span class="text-slate-400">Dosen:</span>
                        <span class="text-slate-800 font-bold ml-1">dosen@kampuslms.test</span>
                    </div>
                    <span class="text-slate-500">password: password</span>
                </div>
                <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-200 flex items-center justify-between">
                    <div>
                        <span class="text-slate-400">Mahasiswa:</span>
                        <span class="text-slate-800 font-bold ml-1">mahasiswa@kampuslms.test</span>
                    </div>
                    <span class="text-slate-500">password: password</span>
                </div>
            </div>
        </div>

        {{-- Card: Kesiapan Milestone M1 (Tugas 1) --}}
        <div class="lms-card p-6">
            <div class="flex items-center gap-2.5 mb-4">
                <span class="w-8 h-8 rounded-lg bg-sky-50 text-[#66A7F2] flex items-center justify-center font-bold">
                    📋
                </span>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Status Kelulusan Milestone M1</h3>
                    <p class="text-xs text-slate-500">Pemeriksaan komponen Tugas 1 (Fondasi Data & CRUD)</p>
                </div>
            </div>

            <div class="space-y-2 text-xs">
                <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                    <span class="text-slate-700 font-medium">1. Skema Database & Migrasi (8 Tabel)</span>
                    <span class="badge-mint text-[10px]">Tersedia & Valid</span>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                    <span class="text-slate-700 font-medium">2. Factory & DatabaseSeeder (>100 submission)</span>
                    <span class="badge-mint text-[10px]">Selesai</span>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                    <span class="text-slate-700 font-medium">3. CRUD Mata Kuliah Penuh</span>
                    <span class="badge-mint text-[10px]">Aktif & Modern</span>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                    <span class="text-slate-700 font-medium">4. CRUD Pengguna (UserController)</span>
                    <span class="badge-amber text-[10px]">Sedang Dikerjakan Teman</span>
                </div>
                <div class="flex items-center justify-between py-1.5">
                    <span class="text-slate-700 font-medium">5. Front-End Design System (DESIGN.md)</span>
                    <span class="badge-mint text-[10px]">Terimplementasi</span>
                </div>
            </div>
        </div>

    </div>

</x-layout>