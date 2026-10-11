<x-layout title="Akses Ditolak">

    <div class="min-h-[65vh] flex flex-col items-center justify-center text-center px-4 py-8">

        {{-- Inline SVG: kunci terkunci, satu bobot garis, tanpa aset placeholder --}}
        <div class="w-16 h-16 rounded-2xl bg-slate-100 border border-slate-200 text-slate-500 flex items-center justify-center mb-5" aria-hidden="true">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
        </div>

        <span class="status status-late mb-3">
            <span class="status-dot" aria-hidden="true"></span>Error 403 <span aria-hidden="true">·</span> Akses ditolak
        </span>

        <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight mb-2">
            Anda tidak memiliki izin
        </h1>

        <p class="text-sm text-slate-600 max-w-md font-normal mb-8 leading-relaxed">
            Halaman ini hanya dapat diakses oleh peran pengguna tertentu. Ganti peran tampilan pada bilah navigasi atau kembali ke dashboard untuk melanjutkan.
        </p>

        <div class="flex flex-wrap items-center justify-center gap-3">
            <a href="{{ route('dashboard') }}" class="btn-primary">
                <svg class="w-4 h-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
                <span>Kembali ke dashboard</span>
            </a>
            <a href="{{ route('courses.index') }}" class="btn-outline">
                <span>Lihat mata kuliah</span>
            </a>
        </div>

    </div>

</x-layout>
