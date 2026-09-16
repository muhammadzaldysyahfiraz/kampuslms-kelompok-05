<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ isset($title) ? $title . ' — KampusLMS' : 'KampusLMS — Sistem Pembelajaran Terpadu' }}</title>

    {{-- Google Fonts: Plus Jakarta Sans & JetBrains Mono --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Asset Bundler Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-full bg-[#F8FAFC] font-sans antialiased text-slate-900 flex flex-col selection:bg-[#111111] selection:text-white">

    {{-- Accessible Skip to Content Link (WCAG 2.4.1 Bypass Blocks) --}}
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:px-4 focus:py-2 focus:bg-[#111111] focus:text-white focus:font-semibold focus:text-xs focus:rounded-md focus:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 transition-all">
        Lewati ke konten utama
    </a>

    {{-- ====================================================================
         TOP NAVIGATION BAR (Sesuai Spesifikasi Modul Kuliah & Minimalist UI)
         ==================================================================== --}}
    <header class="sticky top-0 z-30 bg-white border-b border-slate-200" role="banner">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-14 flex items-center justify-between">
            
            {{-- Logo Brand & Desktop Navbar Links --}}
            <div class="flex items-center gap-6">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 group" aria-label="Beranda KampusLMS">
                    <div class="w-7 h-7 rounded-lg bg-[#111111] text-white flex items-center justify-center font-bold text-xs shadow-2xs group-hover:scale-105 transition-transform" aria-hidden="true">
                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                        </svg>
                    </div>
                    <span class="text-sm font-bold text-slate-900 tracking-tight">
                        Kampus<span class="text-slate-600 font-normal">LMS</span>
                    </span>
                </a>

                {{-- Horizontal Nav Links (Filtered by Role) --}}
                <nav class="hidden md:flex items-center gap-1" aria-label="Navigasi Utama">
                    <a href="{{ route('dashboard') }}" 
                       class="px-3 py-1.5 rounded-md text-xs font-semibold transition-colors {{ request()->routeIs('dashboard') ? 'bg-slate-100 text-slate-950 font-bold' : 'text-slate-700 hover:text-slate-950 hover:bg-slate-50' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('courses.index') }}" 
                       class="px-3 py-1.5 rounded-md text-xs font-semibold transition-colors {{ request()->routeIs('courses.*') ? 'bg-slate-100 text-slate-950 font-bold' : 'text-slate-700 hover:text-slate-950 hover:bg-slate-50' }}">
                        {{ $activeRole === 'dosen' ? 'Mata Kuliah Diampu' : 'Mata Kuliah' }}
                    </a>
                    @if ($activeRole === 'admin' || $activeRole === 'all')
                        <a href="{{ route('users.index') }}" 
                           class="px-3 py-1.5 rounded-md text-xs font-semibold transition-colors {{ request()->routeIs('users.*') ? 'bg-slate-100 text-slate-950 font-bold' : 'text-slate-700 hover:text-slate-950 hover:bg-slate-50' }}">
                            Pengguna
                        </a>
                    @endif
                    <a href="{{ route('tentang') }}" 
                       class="px-3 py-1.5 rounded-md text-xs font-semibold transition-colors {{ request()->routeIs('tentang') ? 'bg-slate-100 text-slate-950 font-bold' : 'text-slate-700 hover:text-slate-950 hover:bg-slate-50' }}">
                        Tentang Kelompok
                    </a>
                </nav>
            </div>

            {{-- Right Context: Interactive Role Switcher & Profile Pill --}}
            <div class="flex items-center gap-3">
                
                {{-- Role Switcher Dropdown --}}
                <div class="relative" id="role-switcher-container">
                    <button 
                        type="button" 
                        onclick="toggleRoleDropdown()" 
                        id="role-switcher-btn"
                        class="inline-flex items-center gap-2 px-2.5 py-1.5 rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 hover:border-slate-300 text-xs font-semibold text-slate-800 transition-all cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-900"
                        aria-haspopup="true"
                        aria-expanded="false"
                        aria-label="Ganti simulasi peran pengguna"
                    >
                        <span class="text-[10px] text-slate-600 font-normal hidden lg:inline">Mode Tampilan:</span>
                        @if ($activeRole === 'admin')
                            <span class="badge-coral text-[11px] py-0.5 px-2">🛡️ Admin</span>
                        @elseif ($activeRole === 'dosen')
                            <span class="badge-sky text-[11px] py-0.5 px-2">👨‍🏫 Dosen</span>
                        @elseif ($activeRole === 'all')
                            <span class="badge-amber text-[11px] py-0.5 px-2">🧪 Evaluasi (All)</span>
                        @else
                            <span class="badge-mint text-[11px] py-0.5 px-2">🎓 Mahasiswa</span>
                        @endif
                        <svg class="w-3.5 h-3.5 text-slate-500 transition-transform" id="role-chevron" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    {{-- Dropdown Menu --}}
                    <div 
                        id="role-dropdown-menu" 
                        class="hidden absolute right-0 mt-2 w-64 rounded-xl bg-white border border-slate-200 shadow-lg py-1.5 z-50 animate-in fade-in zoom-in-95 duration-100"
                        role="menu"
                        aria-orientation="vertical"
                        aria-labelledby="role-switcher-btn"
                    >
                        <div class="px-3 py-2 border-b border-slate-100 text-[11px]">
                            <p class="font-bold text-slate-900">Simulasi Hak Akses (Role)</p>
                            <p class="text-slate-600 font-normal mt-0.5">Ubah perspektif antarmuka pengguna LMS.</p>
                        </div>

                        <a href="{{ route('switch-role', 'mahasiswa') }}" 
                           class="flex items-center justify-between px-3 py-2 text-xs font-semibold hover:bg-slate-50 transition-colors {{ $activeRole === 'mahasiswa' ? 'text-emerald-800 bg-emerald-50/60 font-bold' : 'text-slate-700' }}"
                           role="menuitem">
                            <div class="flex items-center gap-2">
                                <span>🎓</span>
                                <div>
                                    <p class="text-slate-900">Mahasiswa</p>
                                    <p class="text-[10px] text-slate-600 font-normal">Fokus belajar & tugas kuliah</p>
                                </div>
                            </div>
                            @if ($activeRole === 'mahasiswa')
                                <span class="text-emerald-700 text-xs font-bold" aria-hidden="true">✓</span>
                            @endif
                        </a>

                        <a href="{{ route('switch-role', 'dosen') }}" 
                           class="flex items-center justify-between px-3 py-2 text-xs font-semibold hover:bg-slate-50 transition-colors {{ $activeRole === 'dosen' ? 'text-sky-800 bg-sky-50/60 font-bold' : 'text-slate-700' }}"
                           role="menuitem">
                            <div class="flex items-center gap-2">
                                <span>👨‍🏫</span>
                                <div>
                                    <p class="text-slate-900">Dosen Pengampu</p>
                                    <p class="text-[10px] text-slate-600 font-normal">Kelola materi, kelas, & nilai</p>
                                </div>
                            </div>
                            @if ($activeRole === 'dosen')
                                <span class="text-sky-700 text-xs font-bold" aria-hidden="true">✓</span>
                            @endif
                        </a>

                        <a href="{{ route('switch-role', 'admin') }}" 
                           class="flex items-center justify-between px-3 py-2 text-xs font-semibold hover:bg-slate-50 transition-colors {{ $activeRole === 'admin' ? 'text-rose-800 bg-rose-50/60 font-bold' : 'text-slate-700' }}"
                           role="menuitem">
                            <div class="flex items-center gap-2">
                                <span>🛡️</span>
                                <div>
                                    <p class="text-slate-900">Administrator</p>
                                    <p class="text-[10px] text-slate-600 font-normal">Kelola pengguna & kurikulum MK</p>
                                </div>
                            </div>
                            @if ($activeRole === 'admin')
                                <span class="text-rose-700 text-xs font-bold" aria-hidden="true">✓</span>
                            @endif
                        </a>

                        <div class="border-t border-slate-100 my-1"></div>

                        <a href="{{ route('switch-role', 'all') }}" 
                           class="flex items-center justify-between px-3 py-2 text-xs font-semibold hover:bg-slate-50 transition-colors {{ $activeRole === 'all' ? 'text-amber-800 bg-amber-50/60 font-bold' : 'text-slate-700' }}"
                           role="menuitem">
                            <div class="flex items-center gap-2">
                                <span>🧪</span>
                                <div>
                                    <p class="text-slate-900">Mode Evaluasi Praktikum</p>
                                    <p class="text-[10px] text-slate-600 font-normal">Tampilkan semua tombol CRUD</p>
                                </div>
                            </div>
                            @if ($activeRole === 'all')
                                <span class="text-amber-700 text-xs font-bold" aria-hidden="true">✓</span>
                            @endif
                        </a>
                    </div>
                </div>

                {{-- User Avatar Pill (Simulated User) --}}
                <div class="hidden sm:flex items-center gap-2 pl-2 border-l border-slate-200">
                    <div class="w-7 h-7 rounded-lg bg-slate-100 border border-slate-200 text-slate-900 font-bold text-[11px] flex items-center justify-center font-mono flex-shrink-0" aria-hidden="true">
                        {{ strtoupper(substr($activeUser->name ?? 'User', 0, 2)) }}
                    </div>
                    <div class="min-w-0 max-w-[130px]">
                        <p class="text-xs font-bold text-slate-900 truncate leading-tight">
                            {{ $activeUser->name ?? 'User' }}
                        </p>
                        <p class="text-[10px] text-slate-600 font-mono capitalize truncate">
                            {{ $activeUser->role ?? $activeRole }}
                        </p>
                    </div>
                </div>

                {{-- Mobile Hamburger Button --}}
                <button type="button" id="mobile-nav-toggle" onclick="toggleMobileNav()" class="md:hidden p-2 rounded-md text-slate-700 hover:text-slate-950 hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 cursor-pointer" aria-expanded="false" aria-controls="mobile-nav-drawer" aria-label="Buka menu navigasi">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
            </div>

        </div>

        {{-- Mobile Nav Menu (Filtered by Role) --}}
        <nav id="mobile-nav-drawer" class="hidden md:hidden border-t border-slate-200 bg-white px-4 py-3 space-y-1" aria-label="Navigasi Mobile">
            <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-md text-xs font-semibold {{ request()->routeIs('dashboard') ? 'bg-slate-100 text-slate-950 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                Dashboard
            </a>
            <a href="{{ route('courses.index') }}" class="block px-3 py-2 rounded-md text-xs font-semibold {{ request()->routeIs('courses.*') ? 'bg-slate-100 text-slate-950 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                {{ $activeRole === 'dosen' ? 'Mata Kuliah Diampu' : 'Mata Kuliah' }}
            </a>
            @if ($activeRole === 'admin' || $activeRole === 'all')
                <a href="{{ route('users.index') }}" class="block px-3 py-2 rounded-md text-xs font-semibold {{ request()->routeIs('users.*') ? 'bg-slate-100 text-slate-950 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    Pengguna
                </a>
            @endif
            <a href="{{ route('tentang') }}" class="block px-3 py-2 rounded-md text-xs font-semibold {{ request()->routeIs('tentang') ? 'bg-slate-100 text-slate-950 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                Tentang Kelompok
            </a>
        </nav>
    </header>

    {{-- Flash Message Notification Banner (Spot Pastels with ARIA and Auto-Dismiss) --}}
    @if (session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-5 w-full transition-opacity duration-300" id="flash-success" role="status" aria-live="polite">
            <div class="p-3.5 rounded-lg bg-[#EDF3EC] border border-[#245228]/30 text-[#245228] text-xs font-semibold flex items-center justify-between shadow-2xs">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 flex-shrink-0 text-[#245228]" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" onclick="dismissFlash('flash-success')" class="text-[#245228]/70 hover:text-[#245228] p-1.5 rounded hover:bg-[#245228]/10 cursor-pointer transition-colors" aria-label="Tutup notifikasi berhasil">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-5 w-full transition-opacity duration-300" id="flash-error" role="alert" aria-live="assertive">
            <div class="p-3.5 rounded-lg bg-[#FDEBEC] border border-[#8C2220]/30 text-[#8C2220] text-xs font-semibold flex items-center justify-between shadow-2xs">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 flex-shrink-0 text-[#8C2220]" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" onclick="dismissFlash('flash-error')" class="text-[#8C2220]/70 hover:text-[#8C2220] p-1.5 rounded hover:bg-[#8C2220]/10 cursor-pointer transition-colors" aria-label="Tutup peringatan kesalahan">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    @endif

    {{-- Konten Utama via Blade Slot (WCAG 2.4.1 Landmark) --}}
    <main id="main-content" tabindex="-1" class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full focus:outline-none">
        {{ $slot }}
    </main>

    {{-- Footer Halaman Konten (Clean & Calm) --}}
    <footer class="border-t border-slate-200 bg-white py-4 mt-auto" role="contentinfo">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-600">
            <p>&copy; {{ date('Y') }} <strong class="text-slate-800">KampusLMS</strong> • Kelompok 05 (SI2514024)</p>
            <div class="flex items-center gap-2">
                <span>Institut Teknologi Kalimantan</span>
                <span class="text-slate-400" aria-hidden="true">•</span>
                <span class="font-mono text-slate-700 font-medium">Sistem Informasi</span>
            </div>
        </div>
    </footer>

    {{-- Global Script: Navigasi, Keyboard Shortcuts, & Auto-dismiss Flash --}}
    <script>
        function toggleRoleDropdown() {
            const menu = document.getElementById('role-dropdown-menu');
            const btn = document.getElementById('role-switcher-btn');
            const chevron = document.getElementById('role-chevron');
            if (menu && btn) {
                const isHidden = menu.classList.toggle('hidden');
                btn.setAttribute('aria-expanded', !isHidden);
                if (chevron) {
                    chevron.style.transform = isHidden ? 'rotate(0deg)' : 'rotate(180deg)';
                }
            }
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            const container = document.getElementById('role-switcher-container');
            const menu = document.getElementById('role-dropdown-menu');
            const btn = document.getElementById('role-switcher-btn');
            const chevron = document.getElementById('role-chevron');
            if (container && !container.contains(e.target) && menu && !menu.classList.contains('hidden')) {
                menu.classList.add('hidden');
                btn?.setAttribute('aria-expanded', 'false');
                if (chevron) chevron.style.transform = 'rotate(0deg)';
            }
        });

        function toggleMobileNav() {
            const drawer = document.getElementById('mobile-nav-drawer');
            const toggleBtn = document.getElementById('mobile-nav-toggle');
            if (drawer && toggleBtn) {
                const isHidden = drawer.classList.toggle('hidden');
                toggleBtn.setAttribute('aria-expanded', !isHidden);
            }
        }

        function dismissFlash(id) {
            const el = document.getElementById(id);
            if (el) {
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 300);
            }
        }

        // Auto-dismiss flash message after 6 seconds (Nielsen H1: Visibility of System Status)
        document.addEventListener('DOMContentLoaded', () => {
            const successFlash = document.getElementById('flash-success');
            if (successFlash) {
                setTimeout(() => dismissFlash('flash-success'), 6000);
            }
        });

        // Global Keyboard Shortcut: Cmd/Ctrl + K or "/" to focus active search input (Norman Signifier & Mapping)
        document.addEventListener('keydown', (e) => {
            const isK = (e.key === 'k' || e.key === 'K') && (e.metaKey || e.ctrlKey);
            const isSlash = (e.key === '/') && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName);

            if (isK || isSlash) {
                const searchInput = document.querySelector('#course-search-input, #user-search-input, #dashboard-search-input, input[type="search"], input[name="search"]');
                if (searchInput) {
                    e.preventDefault();
                    searchInput.focus();
                    if (searchInput.value) {
                        searchInput.select();
                    }
                }
            }
        });
    </script>

</body>
</html>