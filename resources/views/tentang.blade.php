<x-layout title="Tentang Kelompok">

    {{-- Hero Banner dengan Profil Tim & Spesifikasi Ringkas (Tanpa Kartu Bento Redundan) --}}
    <div class="lms-card p-6 sm:p-7 mb-7 flex flex-col md:flex-row items-center justify-between gap-6 overflow-hidden">
        <div class="max-w-xl">
            <span class="text-[11px] font-mono font-medium text-slate-600 block mb-1">
                SI2514024 <span aria-hidden="true">·</span> Pemrograman Web
            </span>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight mb-1.5">
                Tentang Kelompok 05
            </h1>
            <p class="text-xs sm:text-sm text-slate-700 font-normal leading-relaxed">
                Tim pengembang platform sistem pembelajaran terpadu <strong>KampusLMS</strong> untuk Program Studi Sistem Informasi, Institut Teknologi Kalimantan.
            </p>
            <div class="flex flex-wrap items-center gap-2 mt-4">
                <span class="font-mono text-[11px] font-semibold text-slate-800 bg-slate-100 border border-slate-200 px-2.5 py-1 rounded">
                    SI2514024 · 3 SKS
                </span>
                <span class="font-mono text-[11px] font-semibold text-slate-800 bg-slate-100 border border-slate-200 px-2.5 py-1 rounded">
                    Laravel 12 · Tailwind CSS
                </span>
                <span class="font-mono text-[11px] font-semibold text-slate-800 bg-slate-100 border border-slate-200 px-2.5 py-1 rounded">
                    Sistem Informasi ITK
                </span>
            </div>
        </div>

        <div class="flex-shrink-0 flex flex-col items-center select-none" id="tentang-loving-hero">
            <img src="{{ asset('images/illustrations/opendoodles-loving.svg') }}" 
                 alt="Ilustrasi kolaborasi tim pengembang KampusLMS" 
                 data-anime="loving"
                 class="w-40 sm:w-48 h-auto object-contain diorama-doodle anime-loving-character"
                 loading="lazy"
                 decoding="async">
            <div class="w-28 sm:w-36 h-1.5 rounded-full pointer-events-none -mt-1 anime-loving-shadow"
                 data-anime="loving-shadow"
                 style="background: radial-gradient(ellipse at center, rgba(47, 52, 55, 0.2) 0%, rgba(47, 52, 55, 0) 70%);"></div>
        </div>
    </div>

    {{-- Daftar Anggota Kelompok (Clean Two-Tone Cards Tanpa Footer Repetitif) --}}
    <div>
        <div class="flex items-center justify-between mb-3">
            <h2 class="section-title">Daftar pengembang tim</h2>
            <span class="text-xs text-slate-600 font-mono">5 Mahasiswa</span>
        </div>

        @php
            $members = [
                [
                    'name' => 'Muhammad Rifa Al Rizqul Aulia',
                    'role' => 'Frontend Developer',
                    'nim' => '10241050',
                    'github' => 'rifarizqul',
                    'badge' => 'badge-sky'
                ],
                [
                    'name' => 'Nova Reskianti',
                    'role' => 'Frontend Developer',
                    'nim' => '10241058',
                    'github' => 'Novares06',
                    'badge' => 'badge-sky'
                ],
                [
                    'name' => 'Muhammad Zaldy Syah Firaz',
                    'role' => 'Backend Developer',
                    'nim' => '10241054',
                    'github' => 'muhammadzaldysyahfiraz',
                    'badge' => 'badge-mint'
                ],
                [
                    'name' => 'Muhammad Yuspa Ardiansyah',
                    'role' => 'Backend Developer',
                    'nim' => '10241052',
                    'github' => 'ardiansyahyus24',
                    'badge' => 'badge-mint'
                ],
                [
                    'name' => 'Muhammad Farin Murtadho Syafiq',
                    'role' => 'Database Engineer',
                    'nim' => '10241046',
                    'github' => 'muhammadfarin18',
                    'badge' => 'badge-amber'
                ],
            ];
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ($members as $m)
                <div class="two-tone-card group">
                    <div class="two-tone-body">
                        <div class="flex items-center gap-3.5">
                            <div class="w-10 h-10 rounded-lg bg-slate-100 border border-slate-200 text-slate-900 flex items-center justify-center font-bold text-xs flex-shrink-0" aria-hidden="true">
                                {{ strtoupper(substr($m['name'], 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-sm font-bold text-slate-900 group-hover:text-slate-950 transition-colors truncate">
                                    {{ $m['name'] }}
                                </h3>
                                <span class="{{ $m['badge'] }} text-[11px] mt-1 py-0 px-2 inline-block">
                                    {{ $m['role'] }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="two-tone-footer text-xs font-mono">
                        <span class="text-slate-600">NIM <strong class="text-slate-900">{{ $m['nim'] }}</strong></span>
                        <a href="https://github.com/{{ $m['github'] }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-slate-800 hover:text-slate-950 hover:underline inline-flex items-center gap-1 focus:outline-none focus:underline" aria-label="Profil GitHub {{ $m['name'] }} (buka di tab baru)">
                            <span>{{ '@' . $m['github'] }}</span>
                            <span class="sr-only">(buka profil di tab baru)</span>
                            <svg class="w-3 h-3 text-slate-500" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 19.5 15-15m0 0H8.25m11.25 0v11.25" />
                            </svg>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

</x-layout>
