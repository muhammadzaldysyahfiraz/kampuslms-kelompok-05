<x-layout title="Halaman Tidak Ditemukan">

    <div class="min-h-[65vh] flex flex-col items-center justify-center text-center px-4 py-8">
        
        {{-- Flat Vector SVG Illustration --}}
        <img src="{{ asset('illustrations/not-found-404.svg') }}" alt="Ilustrasi kesalahan 404 halaman perkuliahan tidak ditemukan" class="w-64 h-48 mb-4 object-contain">

        <span class="badge-coral text-[11px] font-mono font-bold uppercase tracking-wider mb-3">
            Error 404 • Not Found
        </span>

        <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight mb-2">
            Halaman Tidak Ditemukan
        </h1>

        <p class="text-sm text-slate-600 max-w-md font-normal mb-8 leading-relaxed">
            Halaman atau tautan akademik yang Anda tuju tidak tersedia atau telah dipindahkan ke direktori lain.
        </p>

        <div class="flex flex-wrap items-center justify-center gap-3">
            <a href="{{ route('dashboard') }}" class="btn-primary">
                <svg class="w-4 h-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
                <span>Kembali ke Dashboard</span>
            </a>
            <a href="{{ route('courses.index') }}" class="btn-outline">
                <svg class="w-4 h-4 text-slate-600" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                </svg>
                <span>Lihat Mata Kuliah</span>
            </a>
        </div>

    </div>

</x-layout>