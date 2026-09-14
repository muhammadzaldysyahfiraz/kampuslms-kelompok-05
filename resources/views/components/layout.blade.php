<!DOCTYPE html>
<html lang="id" class="h-full bg-[#F8FAFC]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ isset($title) ? $title . ' — KampusLMS' : 'KampusLMS — Sistem Pembelajaran Terpadu' }}</title>

    {{-- Google Font Plus Jakarta Sans & Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    {{-- Memuat asset bundler Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full font-sans antialiased text-slate-900 bg-[#F8FAFC] flex flex-col md:flex-row min-h-screen selection:bg-[#FE774C] selection:text-white">

    {{-- ====================================================================
         1. SIDEBAR NAVIGASI UTAMA (Dark Canvas: #1E1E26, Fixed Width: ~240px)
         Sesuai DESIGN.md Section 1.2, 5.1, & 6.5
         ==================================================================== --}}
    <aside class="w-full md:w-64 bg-[#1E1E26] border-r border-[#363644] flex-shrink-0 flex flex-col z-30 md:min-h-screen">
        
        {{-- Brand / Logo Section --}}
        <div class="h-18 px-6 flex items-center justify-between border-b border-[#363644]/70">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 group">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-[#FE774C] to-[#FFC152] flex items-center justify-center text-white font-extrabold text-base shadow-sm shadow-[#FE774C]/30 group-hover:scale-105 transition-transform duration-200">
                    <span>✦</span>
                </div>
                <div class="flex items-center">
                    <span class="text-white font-extrabold text-lg tracking-tight">academy</span>
                    <span class="w-1.5 h-1.5 rounded-full bg-[#FE774C] ml-1 mb-2 animate-pulse"></span>
                </div>
            </a>

            <span class="text-[10px] font-bold tracking-wider text-slate-400 uppercase bg-[#252530] px-2 py-0.5 rounded-md border border-[#363644]">
                ITK
            </span>
        </div>

        {{-- Navigation Menu List --}}
        <div class="flex-1 py-6 px-3 flex flex-col gap-1.5 overflow-y-auto">
            <div class="px-3 pb-2 text-[11px] font-bold tracking-wider text-slate-500 uppercase">
                Menu Utama
            </div>

            {{-- Nav: Dashboard --}}
            <a href="{{ route('dashboard') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('dashboard') ? 'bg-[#252530] text-white border-l-4 border-[#66A7F2] shadow-xs' : 'text-[#94A3B8] hover:text-white hover:bg-[#252530]/60' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('dashboard') ? 'text-[#66A7F2]' : 'text-slate-500' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                </svg>
                <span>Dashboard</span>
            </a>

            {{-- Nav: Mata Kuliah (Courses) --}}
            <a href="{{ route('courses.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('courses.*') ? 'bg-[#252530] text-white border-l-4 border-[#FFC152] shadow-xs' : 'text-[#94A3B8] hover:text-white hover:bg-[#252530]/60' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('courses.*') ? 'text-[#FFC152]' : 'text-slate-500' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                </svg>
                <span>Mata Kuliah</span>
            </a>

            {{-- Nav: Pengguna (Siap terhubung saat modul UserController selesai) --}}
            @if (Route::has('users.index'))
                <a href="{{ route('users.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('users.*') ? 'bg-[#252530] text-white border-l-4 border-[#5DD299] shadow-xs' : 'text-[#94A3B8] hover:text-white hover:bg-[#252530]/60' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('users.*') ? 'text-[#5DD299]' : 'text-slate-500' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                    <span>Pengguna</span>
                </a>
            @else
                <div class="flex items-center justify-between px-3.5 py-2 rounded-xl text-xs text-slate-500 hover:text-slate-400">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                        </svg>
                        <span>Pengguna</span>
                    </div>
                    <span class="text-[10px] bg-slate-800 text-slate-400 px-1.5 py-0.5 rounded">M1 Dev</span>
                </div>
            @endif

            {{-- Nav: Tentang --}}
            <a href="{{ route('tentang') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('tentang') ? 'bg-[#252530] text-white border-l-4 border-[#FE774C] shadow-xs' : 'text-[#94A3B8] hover:text-white hover:bg-[#252530]/60' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('tentang') ? 'text-[#FE774C]' : 'text-slate-500' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                </svg>
                <span>Tentang Kelompok</span>
            </a>
        </div>

        {{-- User Session / Demo Account Card di Footer Sidebar --}}
        <div class="p-4 border-t border-[#363644]/70 bg-[#1A1A22]">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-[#66A7F2] to-[#5DD299] flex items-center justify-center text-white font-bold text-xs ring-2 ring-[#363644]">
                    AD
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold text-white truncate">Admin KampusLMS</p>
                    <p class="text-[11px] text-slate-400 truncate">admin@kampuslms.test</p>
                </div>
                <span class="badge-mint text-[9px] px-1.5 py-0.5">Admin</span>
            </div>
        </div>

    </aside>

    {{-- ====================================================================
         2. AREA KONTEN UTAMA (Light Surface: #F8FAFC)
         ==================================================================== --}}
    <div class="flex-1 flex flex-col min-w-0 bg-[#F8FAFC]">
        
        {{-- Top Header Bar --}}
        <header class="h-18 bg-white border-b border-slate-200/80 px-6 sm:px-8 flex items-center justify-between sticky top-0 z-20 shadow-xs">
            
            {{-- Breadcrumb / Current View Identifier --}}
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-800 transition-colors">KampusLMS</a>
                <span>/</span>
                <span class="text-slate-900 font-bold capitalize">{{ $title ?? 'Dashboard' }}</span>
            </div>

            {{-- Right Badges & Info --}}
            <div class="flex items-center gap-3">
                <div class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-100 border border-slate-200 text-xs font-semibold text-slate-700">
                    <span class="w-2 h-2 rounded-full bg-[#5DD299]"></span>
                    <span>Ganjil 2026/2027</span>
                </div>
                <div class="px-3 py-1.5 rounded-xl bg-[#FE774C]/10 border border-[#FE774C]/30 text-xs font-bold text-[#c2410c]">
                    Kelompok 05
                </div>
            </div>

        </header>

        {{-- Flash Message Notification Banner --}}
        @if (session('success'))
            <div class="mx-6 sm:mx-8 mt-6">
                <div class="p-4 rounded-xl bg-[#5DD299]/15 border border-[#5DD299]/40 text-[#0d6e43] text-sm font-semibold flex items-center justify-between shadow-xs">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 flex-shrink-0 text-[#0d6e43]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600">
                        &times;
                    </button>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="mx-6 sm:mx-8 mt-6">
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-semibold flex items-center justify-between shadow-xs">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 flex-shrink-0 text-rose-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600">
                        &times;
                    </button>
                </div>
            </div>
        @endif

        {{-- Konten Utama yang Diterima via Blade Slot --}}
        <main class="flex-1 px-6 sm:px-8 py-8">
            {{ $slot }}
        </main>

        {{-- Footer Halaman Konten --}}
        <footer class="border-t border-slate-200 bg-white py-5 px-6 sm:px-8 mt-auto">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-500">
                <p>&copy; {{ date('Y') }} <strong>KampusLMS</strong> • Kelompok 05 (SI2514024 Pemrograman Web)</p>
                <p>Institut Teknologi Kalimantan</p>
            </div>
        </footer>

    </div>

</body>
</html>