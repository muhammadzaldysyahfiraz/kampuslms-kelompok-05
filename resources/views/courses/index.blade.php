<x-layout title="Daftar Mata Kuliah">

    {{-- Header Halaman & Aksi Tambah --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                Daftar Mata Kuliah
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Kelola seluruh kurikulum, dosen pengampu, dan status perkuliahan di sistem KampusLMS.
            </p>
        </div>

        <a href="{{ route('courses.create') }}" class="btn-primary flex-shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            <span>Tambah Mata Kuliah</span>
        </a>
    </div>

    {{-- Grid Course Cards Modern (Sesuai DESIGN.md Section 6.2 & image_013.jpg) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
        @forelse ($courses as $index => $course)
            @php
                // Siklus 5 warna aksen multi-accent sesuai DESIGN.md Section 2.1
                $accentColors = ['#FFC152', '#5DD299', '#66A7F2', '#FE774C', '#F1D2F1'];
                $accent = $accentColors[$index % count($accentColors)];
            @endphp

            <div class="lms-card lms-card-hover relative flex flex-col justify-between overflow-hidden p-6 group">
                
                {{-- Decorative Category Color Accent Bar --}}
                <div class="absolute top-0 left-0 right-0 h-1.5" style="background-color: {{ $accent }}"></div>

                <div>
                    {{-- Badges Header Bar: Kode & Status --}}
                    <div class="flex items-center justify-between gap-2 mb-3.5">
                        <span class="font-mono text-xs font-bold px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 border border-slate-200">
                            {{ $course->code }}
                        </span>

                        @if ($course->status === 'active')
                            <span class="badge-mint">Aktif</span>
                        @elseif ($course->status === 'draft')
                            <span class="badge-amber">Draft</span>
                        @else
                            <span class="badge-dark">Diarsipkan</span>
                        @endif
                    </div>

                    {{-- Nama Mata Kuliah --}}
                    <h2 class="text-lg font-extrabold text-slate-900 group-hover:text-[#FE774C] transition-colors line-clamp-1 mb-2">
                        <a href="{{ route('courses.show', $course) }}">
                            {{ $course->name }}
                        </a>
                    </h2>

                    {{-- Deskripsi Singkat --}}
                    <p class="text-xs text-slate-500 line-clamp-2 mb-4">
                        {{ $course->description ?? 'Tidak ada deskripsi rinci untuk mata kuliah ini.' }}
                    </p>
                </div>

                {{-- Footer Kartu: Dosen Pengampu & Aksi --}}
                <div class="pt-4 border-t border-slate-100 mt-2">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-slate-200 to-slate-300 flex items-center justify-center text-slate-700 font-bold text-xs">
                                {{ strtoupper(substr($course->lecturer->name ?? 'D', 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-slate-800 truncate">
                                    {{ $course->lecturer->name ?? 'Belum Ditugaskan' }}
                                </p>
                                <p class="text-[11px] text-slate-400">Dosen Pengampu</p>
                            </div>
                        </div>

                        <span class="text-xs font-extrabold text-slate-700 bg-slate-100 px-2.5 py-1 rounded-md">
                            {{ $course->sks }} SKS
                        </span>
                    </div>

                    {{-- Tombol Aksi Cepat --}}
                    <div class="flex items-center gap-2 pt-1">
                        <a href="{{ route('courses.show', $course) }}" class="flex-1 btn-secondary text-xs py-2 text-center justify-center">
                            Detail
                        </a>
                        <a href="{{ route('courses.edit', $course) }}" class="btn-outline text-xs py-2 px-3">
                            Edit
                        </a>
                        <form action="{{ route('courses.destroy', $course) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus mata kuliah {{ $course->name }}?')" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-danger-soft p-2" title="Hapus Mata Kuliah">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        @empty
            <div class="col-span-full lms-card p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-amber-50 text-[#FFC152] flex items-center justify-center mx-auto mb-4 text-2xl font-bold">
                    ✦
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-1">Belum Ada Mata Kuliah</h3>
                <p class="text-sm text-slate-500 mb-6">Mata kuliah belum ditambahkan atau database belum di-seed.</p>
                <a href="{{ route('courses.create') }}" class="btn-primary">
                    Tambah Mata Kuliah Sekarang
                </a>
            </div>
        @endforelse
    </div>

    {{-- Tabel Ringkasan Data (DESIGN.md Section 6.3) --}}
    <div class="lms-card overflow-hidden">
        <div class="px-6 py-4 bg-slate-50/80 border-b border-slate-200/80 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">
                Tabel Seluruh Mata Kuliah
            </h3>
            <span class="text-xs text-slate-500">Menampilkan {{ $courses->count() }} data</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50/50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Kode</th>
                        <th class="px-6 py-3.5">Nama Mata Kuliah</th>
                        <th class="px-6 py-3.5">SKS</th>
                        <th class="px-6 py-3.5">Dosen Pengampu</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @foreach ($courses as $course)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-6 py-4 font-mono font-bold text-xs text-slate-900">
                                {{ $course->code }}
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-900">
                                <a href="{{ route('courses.show', $course) }}" class="hover:text-[#66A7F2] transition-colors">
                                    {{ $course->name }}
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded bg-slate-100 text-xs font-semibold text-slate-700">
                                    {{ $course->sks }} SKS
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs font-semibold text-slate-800">
                                {{ $course->lecturer->name ?? 'Belum Ditugaskan' }}
                            </td>
                            <td class="px-6 py-4">
                                @if ($course->status === 'active')
                                    <span class="badge-mint text-[10px]">Aktif</span>
                                @elseif ($course->status === 'draft')
                                    <span class="badge-amber text-[10px]">Draft</span>
                                @else
                                    <span class="badge-dark text-[10px]">Diarsipkan</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('courses.show', $course) }}" class="px-2.5 py-1 rounded-md text-xs font-semibold text-[#66A7F2] hover:bg-sky-50 transition-colors">
                                        Lihat
                                    </a>
                                    <a href="{{ route('courses.edit', $course) }}" class="px-2.5 py-1 rounded-md text-xs font-semibold text-slate-700 hover:bg-slate-100 transition-colors">
                                        Edit
                                    </a>
                                    <form action="{{ route('courses.destroy', $course) }}" method="POST" onsubmit="return confirm('Hapus {{ $course->name }}?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1 rounded-md text-xs font-semibold text-rose-600 hover:bg-rose-50 transition-colors">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</x-layout>