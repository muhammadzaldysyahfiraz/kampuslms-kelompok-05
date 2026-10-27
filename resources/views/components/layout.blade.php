@props([
    'title' => null,
    'scene' => null,
    'showBackdrop' => true,
])

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

<body class="min-h-full bg-[#FAF8F5] font-sans antialiased text-slate-900 flex flex-col selection:bg-[#111111] selection:text-white">

    {{-- Latar Belakang Ilustrasi Kampus Minimalis (Multi-Scene per Halaman) --}}
    @if ($showBackdrop)
        <x-campus-sketch :scene="$scene" />
    @endif

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
                        Kampus<span class="text-slate-600 font-normal">LMS</span><span class="text-[#8FA895]" aria-hidden="true">.</span>
                    </span>
                </a>

                {{-- Horizontal Nav Links (Filtered by Role) --}}
                <nav class="hidden md:flex items-center gap-1" aria-label="Navigasi Utama">
                    <a href="{{ route('dashboard') }}" 
                       aria-current="{{ request()->routeIs('dashboard') ? 'page' : 'false' }}"
                       class="nav-link-swiss {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('courses.index') }}" 
                       aria-current="{{ request()->routeIs('courses.*') ? 'page' : 'false' }}"
                       class="nav-link-swiss {{ request()->routeIs('courses.*') ? 'is-active' : '' }}">
                        {{ $activeRole === 'dosen' ? 'Mata Kuliah Diampu' : 'Mata Kuliah' }}
                    </a>
                    @if ($activeRole === 'admin')
                        <a href="{{ route('users.index') }}" 
                           aria-current="{{ request()->routeIs('users.*') ? 'page' : 'false' }}"
                           class="nav-link-swiss {{ request()->routeIs('users.*') ? 'is-active' : '' }}">
                            Pengguna
                        </a>
                    @endif
                    <a href="{{ route('tentang') }}" 
                       aria-current="{{ request()->routeIs('tentang') ? 'page' : 'false' }}"
                       class="nav-link-swiss {{ request()->routeIs('tentang') ? 'is-active' : '' }}">
                        Tentang Kelompok
                    </a>
                </nav>
            </div>

            {{-- Right Context: Profile & Auth Action --}}
            <div class="flex items-center gap-3">
                @auth
                    @php
                        $navUnreadCount = auth()->user()->unreadNotifications()->count();
                        $navRecentNotifications = auth()->user()->notifications()->latest()->take(5)->get();
                    @endphp

                    {{-- Notification Bell Dropdown (Minggu 10) --}}
                    <div class="relative" id="notif-dropdown-wrapper">
                        <button 
                            type="button" 
                            id="notif-bell-btn" 
                            onclick="toggleNotifDropdown()" 
                            class="relative p-2 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors cursor-pointer"
                            aria-expanded="false" 
                            aria-controls="notif-dropdown" 
                            aria-label="Pemberitahuan {{ $navUnreadCount > 0 ? "($navUnreadCount belum dibaca)" : '' }}"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                            </svg>
                            <span 
                                id="notif-badge" 
                                class="{{ $navUnreadCount > 0 ? 'inline-flex' : 'hidden' }} absolute -top-0.5 -right-0.5 items-center justify-center min-w-4 h-4 px-1 rounded-full text-[10px] font-bold font-mono bg-[#FE774C] text-white ring-2 ring-white"
                            >
                                {{ $navUnreadCount > 9 ? '9+' : $navUnreadCount }}
                            </span>
                        </button>

                        {{-- Dropdown Panel --}}
                        <div 
                            id="notif-dropdown" 
                            class="hidden absolute right-0 mt-2 w-80 sm:w-96 rounded-xl bg-white border border-slate-200 shadow-lg z-50 overflow-hidden divide-y divide-slate-100 transition-all"
                        >
                            <div class="px-4 py-3 flex items-center justify-between bg-slate-50/70">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-slate-900">Pemberitahuan</span>
                                    <span id="notif-count-text" class="text-[10px] font-bold font-mono px-1.5 py-0.5 rounded-full bg-slate-200 text-slate-700">
                                        {{ $navUnreadCount }} baru
                                    </span>
                                </div>
                                @if ($navUnreadCount > 0)
                                    <form action="{{ route('notifications.markAllRead') }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-[11px] font-semibold text-[#FE774C] hover:underline cursor-pointer">
                                            Tandai semua dibaca
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <div class="max-h-72 overflow-y-auto divide-y divide-slate-100" id="notif-items-container">
                                @forelse ($navRecentNotifications as $notif)
                                    @php
                                        $isItemUnread = is_null($notif->read_at);
                                        $itemData = $notif->data ?? [];
                                        $itemTitle = $itemData['title'] ?? 'Pemberitahuan';
                                        $itemCourse = $itemData['course_name'] ?? null;
                                    @endphp
                                    <a href="{{ route('notifications.show', $notif) }}" class="block px-4 py-3 hover:bg-slate-50 transition-colors {{ $isItemUnread ? 'bg-[#FFFDFB]' : '' }}">
                                        <div class="flex items-start gap-2.5">
                                            <span class="w-2 h-2 rounded-full mt-1.5 flex-shrink-0 {{ $isItemUnread ? 'bg-[#FE774C]' : 'bg-transparent' }}"></span>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-xs font-bold text-slate-900 truncate">
                                                    {{ $itemTitle }}
                                                </p>
                                                @if ($itemCourse)
                                                    <p class="text-[11px] text-slate-500 truncate mt-0.5">{{ $itemCourse }}</p>
                                                @endif
                                                <p class="text-[10px] text-slate-400 font-mono mt-1">
                                                    {{ $notif->created_at->diffForHumans() }}
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                @empty
                                    <div class="px-4 py-8 text-center text-xs text-slate-500">
                                        Belum ada pemberitahuan baru
                                    </div>
                                @endforelse
                            </div>

                            <div class="p-2 bg-slate-50 text-center">
                                <a href="{{ route('notifications.index') }}" class="block py-1.5 text-xs font-semibold text-slate-700 hover:text-slate-950 transition-colors">
                                    Buka Semua Pemberitahuan &rarr;
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-slate-900 text-white font-bold text-[11px] flex items-center justify-center font-mono flex-shrink-0" aria-hidden="true">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>
                        <div class="hidden sm:block min-w-0 max-w-[160px]">
                            <p class="text-xs font-bold text-slate-900 truncate leading-tight">
                                {{ auth()->user()->name }}
                            </p>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="status status-{{ auth()->user()->role }} text-[10px] py-0 leading-none">
                                    <span class="status-dot" aria-hidden="true"></span>
                                    {{ ucfirst(auth()->user()->role) }}
                                </span>
                                @if (auth()->user()->nim_nip)
                                    <span class="text-[10px] text-slate-400 font-mono">· {{ auth()->user()->nim_nip }}</span>
                                @endif
                            </div>
                        </div>

                        {{-- Tombol Logout --}}
                        <form action="{{ route('logout') }}" method="POST" class="inline ml-1">
                            @csrf
                            <button 
                                type="submit" 
                                title="Keluar dari akun (Logout)" 
                                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg border border-slate-200 bg-slate-50 hover:bg-rose-50 hover:border-rose-200 hover:text-rose-700 text-xs font-semibold text-slate-700 transition-colors cursor-pointer"
                                aria-label="Keluar dari akun"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                                </svg>
                                <span class="hidden sm:inline">Keluar</span>
                            </button>
                        </form>
                    </div>
                @else
                    <div class="flex items-center gap-2">
                        <a href="{{ route('login') }}" class="px-3.5 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold shadow-xs transition-colors">
                            Masuk
                        </a>
                    </div>
                @endauth

                {{-- Mobile Hamburger Button --}}
                <button type="button" id="mobile-nav-toggle" onclick="toggleMobileNav()" class="md:hidden inline-flex items-center justify-center w-11 h-11 -mr-2 rounded-lg text-slate-700 hover:text-slate-950 hover:bg-slate-100 cursor-pointer" aria-expanded="false" aria-controls="mobile-nav-drawer" aria-label="Buka menu navigasi">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
            </div>

        </div>

        {{-- Mobile Nav Menu (Filtered by Role, 44px touch targets) --}}
        <nav id="mobile-nav-drawer" class="hidden md:hidden border-t border-slate-200 bg-white px-4 py-3 space-y-1" aria-label="Navigasi Mobile">
            <a href="{{ route('dashboard') }}" aria-current="{{ request()->routeIs('dashboard') ? 'page' : 'false' }}" class="flex items-center min-h-11 px-3 rounded-lg text-xs font-semibold {{ request()->routeIs('dashboard') ? 'bg-slate-100 text-slate-950 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                Dashboard
            </a>
            <a href="{{ route('courses.index') }}" aria-current="{{ request()->routeIs('courses.*') ? 'page' : 'false' }}" class="flex items-center min-h-11 px-3 rounded-lg text-xs font-semibold {{ request()->routeIs('courses.*') ? 'bg-slate-100 text-slate-950 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                {{ $activeRole === 'dosen' ? 'Mata Kuliah Diampu' : 'Mata Kuliah' }}
            </a>
            @if ($activeRole === 'admin')
                <a href="{{ route('users.index') }}" aria-current="{{ request()->routeIs('users.*') ? 'page' : 'false' }}" class="flex items-center min-h-11 px-3 rounded-lg text-xs font-semibold {{ request()->routeIs('users.*') ? 'bg-slate-100 text-slate-950 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    Pengguna
                </a>
            @endif
            <a href="{{ route('tentang') }}" aria-current="{{ request()->routeIs('tentang') ? 'page' : 'false' }}" class="flex items-center min-h-11 px-3 rounded-lg text-xs font-semibold {{ request()->routeIs('tentang') ? 'bg-slate-100 text-slate-950 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                Tentang Kelompok
            </a>

            @auth
                <a href="{{ route('notifications.index') }}" aria-current="{{ request()->routeIs('notifications.*') ? 'page' : 'false' }}" class="flex items-center justify-between min-h-11 px-3 rounded-lg text-xs font-semibold {{ request()->routeIs('notifications.*') ? 'bg-slate-100 text-slate-950 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    <span>Pemberitahuan</span>
                    @if ($navUnreadCount > 0)
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold font-mono bg-[#FE774C] text-white">
                            {{ $navUnreadCount }}
                        </span>
                    @endif
                </a>
            @endauth

            @auth
                <div class="pt-2 mt-2 border-t border-slate-100 flex items-center justify-between px-3">
                    <span class="text-xs text-slate-600 truncate">{{ auth()->user()->name }} ({{ auth()->user()->role }})</span>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center min-h-11 px-2 text-xs text-rose-600 hover:text-rose-700 font-semibold cursor-pointer">
                            Keluar
                        </button>
                    </form>
                </div>
            @else
                <div class="pt-2 mt-2 border-t border-slate-100 px-3">
                    <a href="{{ route('login') }}" class="flex items-center justify-center min-h-11 rounded-lg bg-slate-900 text-white text-xs font-semibold">
                        Masuk
                    </a>
                </div>
            @endauth
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
        function toggleNotifDropdown() {
            const dropdown = document.getElementById('notif-dropdown');
            const btn = document.getElementById('notif-bell-btn');
            if (!dropdown || !btn) return;
            const isHidden = dropdown.classList.contains('hidden');
            if (isHidden) {
                dropdown.classList.remove('hidden');
                btn.setAttribute('aria-expanded', 'true');
            } else {
                dropdown.classList.add('hidden');
                btn.setAttribute('aria-expanded', 'false');
            }
        }

        // Close dropdown when clicked outside
        document.addEventListener('click', (e) => {
            const wrapper = document.getElementById('notif-dropdown-wrapper');
            const dropdown = document.getElementById('notif-dropdown');
            const btn = document.getElementById('notif-bell-btn');
            if (wrapper && dropdown && !wrapper.contains(e.target)) {
                dropdown.classList.add('hidden');
                if (btn) btn.setAttribute('aria-expanded', 'false');
            }
        });

        @auth
        // Polling status notifikasi berkala (setiap 30 detik)
        setInterval(() => {
            fetch('{{ route('notifications.index') }}', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data && typeof data.unread_count !== 'undefined') {
                    const badge = document.getElementById('notif-badge');
                    const countText = document.getElementById('notif-count-text');
                    if (badge) {
                        if (data.unread_count > 0) {
                            badge.textContent = data.unread_count > 9 ? '9+' : data.unread_count;
                            badge.classList.remove('hidden');
                            badge.classList.add('inline-flex');
                        } else {
                            badge.classList.add('hidden');
                            badge.classList.remove('inline-flex');
                        }
                    }
                    if (countText) {
                        countText.textContent = data.unread_count + ' baru';
                    }
                }
            })
            .catch(() => {});
        }, 30000);
        @endauth

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