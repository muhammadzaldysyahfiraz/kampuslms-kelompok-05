<x-layout title="Tentang Kelompok">

    {{-- Hero Banner dengan Profil Tim --}}
    <div class="lms-card p-6 sm:p-7 mb-6 flex flex-col md:flex-row items-center justify-between gap-6 overflow-hidden">
        <div class="max-w-xl">
            <span class="text-[11px] font-mono font-medium text-slate-600 uppercase tracking-wide block mb-1">
                SI2514024 • Pemrograman Web
            </span>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight mb-1.5">
                Tentang Kelompok 05
            </h1>
            <p class="text-xs sm:text-sm text-slate-700 font-normal leading-relaxed">
                Tim pengembang platform sistem pembelajaran terpadu <strong>KampusLMS</strong> berbasis Laravel 12 dan arsitektur UI/UX Utilitarian Minimalist untuk Institut Teknologi Kalimantan.
            </p>
        </div>

        <div class="flex-shrink-0">
            <img src="{{ asset('illustrations/team-collaboration.svg') }}" alt="Ilustrasi tim pengembang KampusLMS berkolaborasi di lingkungan modern" class="w-52 h-28 object-contain">
        </div>
    </div>

    {{-- Bento Grid Profil Proyek & Tim --}}
    <div class="space-y-6">

        {{-- Bento Grid 1: Ringkasan Proyek & Spesifikasi --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="lms-card p-4">
                <div class="flex items-center gap-2.5 mb-1.5">
                    <div class="w-7 h-7 rounded bg-slate-100 text-slate-800 flex items-center justify-center font-bold" aria-hidden="true">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-600 block font-semibold uppercase tracking-wider">Mata Kuliah</span>
                        <span class="text-xs font-bold text-slate-900">Pemrograman Web</span>
                    </div>
                </div>
                <p class="text-[11px] text-slate-600 font-mono">SI2514024 • 3 SKS</p>
            </div>

            <div class="lms-card p-4">
                <div class="flex items-center gap-2.5 mb-1.5">
                    <div class="w-7 h-7 rounded bg-slate-100 text-slate-800 flex items-center justify-center font-bold" aria-hidden="true">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-600 block font-semibold uppercase tracking-wider">Kelompok</span>
                        <span class="text-xs font-bold text-slate-900">Kelompok 05</span>
                    </div>
                </div>
                <p class="text-[11px] text-slate-600 font-mono">5 Anggota Mahasiswa</p>
            </div>

            <div class="lms-card p-4">
                <div class="flex items-center gap-2.5 mb-1.5">
                    <div class="w-7 h-7 rounded bg-slate-100 text-slate-800 flex items-center justify-center font-bold" aria-hidden="true">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-600 block font-semibold uppercase tracking-wider">Teknologi</span>
                        <span class="text-xs font-bold text-slate-900">Laravel 12 & Tailwind</span>
                    </div>
                </div>
                <p class="text-[11px] text-slate-600 font-mono">PHP 8.2+ • Vite 6</p>
            </div>

            <div class="lms-card p-4">
                <div class="flex items-center gap-2.5 mb-1.5">
                    <div class="w-7 h-7 rounded bg-slate-100 text-slate-800 flex items-center justify-center font-bold" aria-hidden="true">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-600 block font-semibold uppercase tracking-wider">Institusi</span>
                        <span class="text-xs font-bold text-slate-900">ITK Balikpapan</span>
                    </div>
                </div>
                <p class="text-[11px] text-slate-600 font-mono">Sistem Informasi</p>
            </div>
        </div>

        {{-- Bento Grid 2: Anggota Kelompok Card List --}}
        <div>
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Daftar Pengembang Tim</h2>
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
                    <div class="lms-card p-5 flex flex-col justify-between group hover:border-slate-300 transition-colors">
                        <div>
                            <div class="flex items-center gap-3.5 mb-3.5">
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

                            <div class="space-y-1 py-2 text-xs font-normal text-slate-700">
                                <div class="flex justify-between">
                                    <span class="text-slate-600">NIM:</span>
                                    <span class="font-mono font-semibold text-slate-900">{{ $m['nim'] }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-600">GitHub:</span>
                                    <a href="https://github.com/{{ $m['github'] }}" target="_blank" rel="noopener noreferrer" class="font-mono font-semibold text-slate-800 hover:text-slate-950 hover:underline inline-flex items-center gap-1 focus:outline-none focus:underline" aria-label="Profil GitHub {{ $m['name'] }} (buka di tab baru)">
                                        <span>@ {{ $m['github'] }}</span>
                                        <span class="sr-only">(buka profil di tab baru)</span>
                                        <svg class="w-3 h-3 text-slate-500" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 19.5 15-15m0 0H8.25m11.25 0v11.25" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-100 mt-2 flex items-center justify-between text-[11px] text-slate-600 font-normal">
                            <span>Mahasiswa Aktif ITK</span>
                            <span class="text-emerald-800 font-semibold flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-emerald-700" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                                <span>Terverifikasi</span>
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

</x-layout>
