<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    {{-- Pengaturan viewport agar layout responsif di berbagai ukuran layar perangkat --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Nilai title dinamis dikirim dari child view; jika tidak ada, fallback ke judul bawaan --}}
    <title>{{ isset($title) ? $title . ' — KampusLMS' : 'KampusLMS — Sistem Pembelajaran Terpadu' }}</title>

    {{-- Google Font Plus Jakarta Sans untuk tipografi antarmuka modern --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Memuat asset bundler Vite (Tailwind CSS dan JavaScript aplikasi) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col font-sans antialiased selection:bg-indigo-500 selection:text-white">

    {{-- HEADER & NAVBAR UTAMA: Mengatur identitas brand dan navigasi sentral --}}
    <header class="bg-white/90 backdrop-blur-md border-b border-slate-200/80 sticky top-0 z-50 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                {{-- Identitas Brand KampusLMS --}}
                <div class="flex items-center gap-3">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 font-bold text-xl text-slate-900 tracking-tight group">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center text-white shadow-sm shadow-indigo-200 group-hover:scale-105 transition-transform duration-200">
                            {{-- Ikon Topi Akademik / Buku (SVG Semantik) --}}
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342" />
                            </svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="leading-tight">Kampus<span class="text-indigo-600">LMS</span></span>
                        </div>
                    </a>
                    <span class="hidden sm:inline-flex text-[11px] font-semibold uppercase tracking-wider bg-indigo-50 text-indigo-700 px-2.5 py-0.5 rounded-full border border-indigo-200/60">
                        Kelompok 05
                    </span>
                </div>

                {{-- Menu Navigasi dengan Active State via Named Route --}}
                <nav class="flex items-center gap-1.5 sm:gap-2">
                    {{-- Navigasi ke Dashboard --}}
                    <a href="{{ route('dashboard') }}" 
                       class="{{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-700 font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 font-medium' }} px-3 sm:px-3.5 py-1.5 sm:py-2 rounded-lg text-sm transition-all duration-150">
                        Dashboard
                    </a>

                    {{-- Navigasi ke Daftar Mata Kuliah --}}
                    <a href="{{ route('courses.index') }}" 
                       class="{{ request()->routeIs('courses.*') ? 'bg-indigo-50 text-indigo-700 font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 font-medium' }} px-3 sm:px-3.5 py-1.5 sm:py-2 rounded-lg text-sm transition-all duration-150">
                        Mata Kuliah
                    </a>

                    {{-- Navigasi ke Halaman Tentang --}}
                    <a href="{{ route('tentang') }}" 
                       class="{{ request()->routeIs('tentang') ? 'bg-indigo-50 text-indigo-700 font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 font-medium' }} px-3 sm:px-3.5 py-1.5 sm:py-2 rounded-lg text-sm transition-all duration-150">
                        Tentang
                    </a>
                </nav>

                {{-- Status Semester / Badge Informasi Akademik --}}
                <div class="hidden md:flex items-center gap-2">
                    <span class="text-xs text-slate-500 font-medium bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200/60">
                        Semester Ganjil 2026/2027
                    </span>
                </div>

            </div>
        </div>
    </header>

    {{-- KONTEN UTAMA: Menampung seluruh isi view melalui Blade Slot --}}
    <main class="lms-main flex-grow w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
        {{ $slot }}
    </main>

    {{-- FOOTER APLIKASI: Informasi hak cipta, mata kuliah, dan kampus --}}
    <footer class="bg-white border-t border-slate-200 mt-auto py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs sm:text-sm text-slate-500">
            <div>
                <p>&copy; {{ date('Y') }} <strong>KampusLMS</strong> • Kelompok 05 (SI2514024 Pemrograman Web)</p>
            </div>
            <div class="flex items-center gap-4">
                <span>Institut Teknologi Kalimantan</span>
            </div>
        </div>
    </footer>

</body>
</html>