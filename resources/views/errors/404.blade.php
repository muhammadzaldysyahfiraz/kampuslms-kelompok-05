<x-layout title="Halaman Tidak Ditemukan" scene="error">

    <div class="min-h-[70vh] flex flex-col items-center justify-center text-center px-4 py-12 relative z-10">
        
        {{-- Open Doodles Zombieing (Mahasiswa tersesat di koridor kampus) --}}
        <div class="relative mb-2">
            <x-diorama.doodle name="zombieing" size="240" class="w-48 sm:w-56 h-auto mx-auto filter drop-shadow-xs" />
            <div class="w-44 h-2 mx-auto rounded-full mt-1 pointer-events-none" style="background: radial-gradient(ellipse at center, rgba(47, 52, 55, 0.2) 0%, rgba(47, 52, 55, 0) 70%);"></div>
        </div>

        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md text-xs font-mono font-medium bg-slate-100 border border-slate-200 text-slate-700 mb-3">
            <span class="w-1.5 h-1.5 rounded-full bg-slate-400" aria-hidden="true"></span>
            Error 404 <span aria-hidden="true">·</span> Tidak Ditemukan
        </span>

        <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight mb-2">
            Tersesat di Koridor Kampus
        </h1>

        <p class="text-xs sm:text-sm text-slate-600 max-w-md mx-auto font-normal mb-8 leading-relaxed">
            Halaman atau tautan materi perkuliahan yang Anda tuju tidak tersedia atau telah dipindahkan ke direktori lain.
        </p>

        <div class="flex flex-wrap items-center justify-center gap-3">
            <a href="{{ route('dashboard') }}" class="btn-primary">
                <svg class="w-4 h-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
                <span>Kembali ke dashboard</span>
            </a>
            <a href="{{ route('courses.index') }}" class="btn-outline">
                <svg class="w-4 h-4 text-slate-600" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                </svg>
                <span>Lihat mata kuliah</span>
            </a>
        </div>

    </div>

</x-layout>
