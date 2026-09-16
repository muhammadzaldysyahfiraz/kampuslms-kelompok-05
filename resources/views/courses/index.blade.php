<x-layout title="Daftar Mata Kuliah">

    {{-- Header Halaman & Aksi Tambah --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                {{ $activeRole === 'dosen' ? 'Mata Kuliah Diampu' : 'Daftar Mata Kuliah' }}
            </h1>
            <p class="text-xs text-slate-600 mt-0.5">
                @if ($activeRole === 'mahasiswa')
                    Jelajahi silabus perkuliahan, unduh modul bahan ajar, dan pantau tugas pada kelas Anda.
                @elseif ($activeRole === 'dosen')
                    Kelola materi pembelajaran, silabus, dan penugasan kelas yang Anda ampu semester ini.
                @else
                    Kelola kurikulum pembelajaran, dosen pengampu, dan status perkuliahan di sistem KampusLMS.
                @endif
            </p>
        </div>

        @if ($activeRole !== 'mahasiswa')
            <a href="{{ route('courses.create') }}" class="btn-primary flex-shrink-0 text-xs py-2">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Tambah Mata Kuliah</span>
            </a>
        @else
            <div class="flex items-center gap-2 flex-shrink-0">
                <span class="badge-mint text-xs py-1 px-2.5 font-medium">🎓 Mahasiswa Terdaftar</span>
            </div>
        @endif
    </div>

    {{-- Search, Filter, & View Mode Switcher Bar --}}
    <div class="lms-card p-3 mb-6 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
        {{-- Live Search Input --}}
        <div class="relative flex-1">
            <svg class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-slate-500" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <input 
                type="text" 
                id="course-search-input" 
                onkeyup="filterCourseList()" 
                placeholder="Cari nama, kode mata kuliah, atau nama dosen pengampu..." 
                aria-label="Cari nama, kode mata kuliah, atau nama dosen pengampu"
                class="w-full pl-8.5 pr-12 py-1.5 rounded-lg bg-slate-50 border border-slate-200 text-xs text-slate-900 placeholder:text-slate-500 focus:outline-none focus:border-slate-900 focus:bg-white transition-colors"
            >
            <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" aria-hidden="true">
                <kbd class="hidden sm:inline-block px-1.5 py-0.5 text-[10px] font-mono text-slate-600 bg-white border border-slate-200 rounded">⌘K</kbd>
            </div>
        </div>

        <div class="flex items-center justify-between sm:justify-end gap-2">
            {{-- Status Filter Chips --}}
            <div class="flex items-center gap-1" id="status-filter-buttons" role="group" aria-label="Filter status mata kuliah">
                <button type="button" onclick="setStatusFilter('all')" class="status-filter-btn px-2.5 py-1.5 rounded-md text-xs font-semibold transition-all bg-[#111111] text-white cursor-pointer" data-status="all" aria-pressed="true">
                    Semua ({{ $courses->count() }})
                </button>
                <button type="button" onclick="setStatusFilter('active')" class="status-filter-btn px-2.5 py-1.5 rounded-md text-xs font-semibold transition-all bg-slate-100 text-slate-700 hover:bg-slate-200 cursor-pointer" data-status="active" aria-pressed="false">
                    Aktif ({{ $courses->where('status', 'active')->count() }})
                </button>
                <button type="button" onclick="setStatusFilter('draft')" class="status-filter-btn px-2.5 py-1.5 rounded-md text-xs font-semibold transition-all bg-slate-100 text-slate-700 hover:bg-slate-200 cursor-pointer" data-status="draft" aria-pressed="false">
                    Draft ({{ $courses->where('status', 'draft')->count() }})
                </button>
            </div>

            {{-- View Mode Switcher (Grid vs Table) --}}
            <div class="flex items-center bg-slate-100 p-0.5 rounded-md border border-slate-200" role="group" aria-label="Pilihan tampilan data">
                <button type="button" onclick="switchViewMode('grid')" id="view-grid-btn" class="p-1 rounded text-slate-900 bg-white shadow-2xs transition-all cursor-pointer" title="Tampilan Kartu Grid" aria-label="Tampilan Kartu Grid" aria-pressed="true">
                    <svg class="w-3.5 h-3.5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                    </svg>
                </button>
                <button type="button" onclick="switchViewMode('table')" id="view-table-btn" class="p-1 rounded text-slate-600 hover:text-slate-900 transition-all cursor-pointer" title="Tampilan Tabel Data" aria-label="Tampilan Tabel Data" aria-pressed="false">
                    <svg class="w-3.5 h-3.5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- VIEW 1: Grid Course Cards Bento (Default View) --}}
    <div id="courses-grid-view">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mb-8" id="courses-grid">
            @forelse ($courses as $course)
                <div class="course-card lms-card lms-card-hover flex flex-col justify-between p-5 group" data-status="{{ strtolower($course->status) }}" data-keywords="{{ strtolower($course->code . ' ' . $course->name . ' ' . ($course->lecturer->name ?? '')) }}">
                    
                    <div>
                        {{-- Badges Header Bar: Kode, SKS & Status --}}
                        <div class="flex items-center justify-between gap-2 mb-2.5">
                            <div class="flex items-center gap-1.5">
                                <span class="font-mono text-[11px] font-semibold text-slate-800 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">
                                    {{ $course->code }}
                                </span>
                                <span class="font-mono text-[11px] font-medium text-slate-600">
                                    {{ $course->sks }} SKS
                                </span>
                            </div>

                            @if ($course->status === 'active')
                                <span class="badge-mint text-[11px]">Aktif</span>
                            @elseif ($course->status === 'draft')
                                <span class="badge-amber text-[11px]">Draft</span>
                            @else
                                <span class="badge-dark text-[11px]">Diarsipkan</span>
                            @endif
                        </div>

                        {{-- Nama Mata Kuliah --}}
                        <h2 class="text-sm font-bold text-slate-900 group-hover:text-slate-700 transition-colors line-clamp-1 mb-1">
                            <a href="{{ route('courses.show', $course) }}" class="focus:outline-none focus:underline">
                                {{ $course->name }}
                            </a>
                        </h2>

                        {{-- Deskripsi Singkat --}}
                        <p class="text-xs text-slate-600 line-clamp-2 mb-4 leading-relaxed font-normal">
                            {{ $course->description ?? 'Kurikulum pembelajaran terpadu untuk program studi sistem informasi.' }}
                        </p>
                    </div>

                    {{-- Footer Kartu: Dosen Pengampu & Accessible Inline Actions --}}
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                        <div class="min-w-0 pr-2">
                            <p class="font-semibold text-slate-800 truncate text-[11px]">
                                {{ $course->lecturer->name ?? 'Belum Ditugaskan' }}
                            </p>
                            <p class="text-[11px] text-slate-600">Dosen Pengampu</p>
                        </div>

                        {{-- Discrete Touch-Friendly Actions --}}
                        <div class="flex items-center gap-1.5 flex-shrink-0">
                            @if ($activeRole === 'mahasiswa')
                                <a href="{{ route('courses.show', $course) }}" class="px-2.5 py-1 rounded text-xs font-semibold text-slate-800 hover:text-slate-950 hover:bg-slate-100 transition-colors inline-flex items-center gap-1" aria-label="Buka materi kuliah {{ $course->name }}">
                                    <span>Buka</span>
                                    <span aria-hidden="true">→</span>
                                </a>
                            @else
                                <a href="{{ route('courses.show', $course) }}" class="px-2 py-1 rounded text-xs font-semibold text-slate-800 hover:text-slate-950 hover:bg-slate-100 transition-colors" aria-label="Buka detail mata kuliah {{ $course->name }}">
                                    Buka
                                </a>
                                <span class="text-slate-300" aria-hidden="true">•</span>
                                <a href="{{ route('courses.edit', $course) }}" class="px-2 py-1 rounded text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors" aria-label="Edit mata kuliah {{ $course->name }}">
                                    Edit
                                </a>
                            @endif
                        </div>
                    </div>

                </div>
            @empty
                <div class="col-span-full lms-card p-8 text-center">
                    <p class="text-xs text-slate-600 mb-3">Belum ada mata kuliah yang terdaftar.</p>
                    @if ($activeRole !== 'mahasiswa')
                        <a href="{{ route('courses.create') }}" class="btn-primary text-xs">
                            Tambah Mata Kuliah Baru
                        </a>
                    @endif
                </div>
            @endforelse
        </div>

        {{-- Zero-Result Search State for Grid View --}}
        <div id="courses-grid-zero-state" class="hidden lms-card p-8 text-center my-6" role="status" aria-live="polite">
            <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-600 mx-auto flex items-center justify-center mb-3" aria-hidden="true">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </div>
            <h3 class="text-sm font-bold text-slate-900 mb-1">Tidak ada mata kuliah yang cocok</h3>
            <p class="text-xs text-slate-600 max-w-sm mx-auto mb-4 font-normal">
                Tidak ditemukan mata kuliah yang sesuai dengan kata kunci atau filter status yang Anda pilih.
            </p>
            <button type="button" onclick="resetCourseFilters()" class="btn-outline text-xs inline-flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
                <span>Reset Pencarian & Filter</span>
            </button>
        </div>
    </div>

    {{-- VIEW 2: Tabel Ringkasan Data --}}
    <div id="courses-table-view" class="hidden">
        <div class="lms-card overflow-hidden mb-6">
            <div class="px-5 py-3 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                    Tabel Seluruh Mata Kuliah
                </h3>
                <span class="text-xs text-slate-600 font-mono">{{ $courses->count() }} Data</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs" id="courses-table" aria-label="Tabel daftar lengkap mata kuliah">
                    <thead class="bg-slate-50/50 text-[11px] font-bold text-slate-700 uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th scope="col" class="px-5 py-2.5">Kode</th>
                            <th scope="col" class="px-5 py-2.5">Nama Mata Kuliah</th>
                            <th scope="col" class="px-5 py-2.5">SKS</th>
                            <th scope="col" class="px-5 py-2.5">Dosen Pengampu</th>
                            <th scope="col" class="px-5 py-2.5">Status</th>
                            <th scope="col" class="px-5 py-2.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700" id="courses-tbody">
                        @foreach ($courses as $course)
                            <tr class="course-row hover:bg-slate-50 transition-colors" data-status="{{ strtolower($course->status) }}" data-keywords="{{ strtolower($course->code . ' ' . $course->name . ' ' . ($course->lecturer->name ?? '')) }}">
                                <td class="px-5 py-3 font-mono font-semibold text-slate-900">
                                    {{ $course->code }}
                                </td>
                                <td class="px-5 py-3 font-semibold text-slate-900">
                                    <a href="{{ route('courses.show', $course) }}" class="hover:underline focus:outline-none focus:underline">
                                        {{ $course->name }}
                                    </a>
                                </td>
                                <td class="px-5 py-3 font-mono text-slate-600">
                                    {{ $course->sks }} SKS
                                </td>
                                <td class="px-5 py-3 text-slate-700">
                                    {{ $course->lecturer->name ?? 'Belum Ditugaskan' }}
                                </td>
                                <td class="px-5 py-3">
                                    @if ($course->status === 'active')
                                        <span class="badge-mint text-[11px]">Aktif</span>
                                    @elseif ($course->status === 'draft')
                                        <span class="badge-amber text-[11px]">Draft</span>
                                    @else
                                        <span class="badge-dark text-[11px]">Diarsipkan</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <div class="inline-flex items-center gap-1">
                                        <a href="{{ route('courses.show', $course) }}" class="px-2 py-1 rounded text-xs font-semibold text-slate-800 hover:text-slate-950 hover:bg-slate-100 transition-colors" aria-label="Lihat detail {{ $course->name }}">
                                            {{ $activeRole === 'mahasiswa' ? 'Buka' : 'Lihat' }}
                                        </a>
                                        @if ($activeRole !== 'mahasiswa')
                                            <span class="text-slate-300" aria-hidden="true">•</span>
                                            <a href="{{ route('courses.edit', $course) }}" class="px-2 py-1 rounded text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors" aria-label="Edit {{ $course->name }}">
                                                Edit
                                            </a>
                                            <span class="text-slate-300" aria-hidden="true">•</span>
                                            <form action="{{ route('courses.destroy', $course) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus mata kuliah {{ $course->name }}?')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-2 py-1 rounded text-xs font-medium text-rose-700 hover:text-rose-900 hover:bg-rose-50 transition-colors cursor-pointer" aria-label="Hapus mata kuliah {{ $course->name }}">
                                                    Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        
                        {{-- Zero-Result Search State for Table View --}}
                        <tr id="courses-table-zero-state" class="hidden" role="status" aria-live="polite">
                            <td colspan="6" class="px-5 py-8 text-center text-slate-600">
                                <p class="font-bold text-slate-800 mb-1">Tidak ada mata kuliah yang cocok</p>
                                <p class="text-xs text-slate-600 mb-3 font-normal">Tidak ditemukan hasil sesuai kata kunci pencarian atau filter status.</p>
                                <button type="button" onclick="resetCourseFilters()" class="btn-outline text-xs inline-flex items-center gap-1.5">
                                    <span>Reset Filter & Pencarian</span>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- JavaScript Live Filter & View Mode Switcher --}}
    <script>
        let currentStatus = 'all';

        function setStatusFilter(status) {
            currentStatus = status;
            document.querySelectorAll('.status-filter-btn').forEach(btn => {
                if (btn.getAttribute('data-status') === status) {
                    btn.className = 'status-filter-btn px-2.5 py-1.5 rounded-md text-xs font-semibold transition-all bg-[#111111] text-white cursor-pointer';
                    btn.setAttribute('aria-pressed', 'true');
                } else {
                    btn.className = 'status-filter-btn px-2.5 py-1.5 rounded-md text-xs font-semibold transition-all bg-slate-100 text-slate-700 hover:bg-slate-200 cursor-pointer';
                    btn.setAttribute('aria-pressed', 'false');
                }
            });
            filterCourseList();
        }

        function filterCourseList() {
            const query = (document.getElementById('course-search-input')?.value || '').toLowerCase().trim();
            const cards = document.querySelectorAll('.course-card');
            const rows = document.querySelectorAll('.course-row');
            
            let visibleCards = 0;
            let visibleRows = 0;

            cards.forEach(card => {
                const status = card.getAttribute('data-status') || '';
                const keywords = card.getAttribute('data-keywords') || '';
                const matchStatus = (currentStatus === 'all' || status === currentStatus);
                const matchQuery = (query === '' || keywords.includes(query));
                const isVisible = (matchStatus && matchQuery);
                card.style.display = isVisible ? '' : 'none';
                if (isVisible) visibleCards++;
            });

            rows.forEach(row => {
                const status = row.getAttribute('data-status') || '';
                const keywords = row.getAttribute('data-keywords') || '';
                const matchStatus = (currentStatus === 'all' || status === currentStatus);
                const matchQuery = (query === '' || keywords.includes(query));
                const isVisible = (matchStatus && matchQuery);
                row.style.display = isVisible ? '' : 'none';
                if (isVisible) visibleRows++;
            });

            // Toggle Zero-Result State (Nielsen H1 & H10)
            const gridZeroState = document.getElementById('courses-grid-zero-state');
            if (gridZeroState) {
                gridZeroState.classList.toggle('hidden', visibleCards > 0 || cards.length === 0);
            }

            const tableZeroState = document.getElementById('courses-table-zero-state');
            if (tableZeroState) {
                tableZeroState.classList.toggle('hidden', visibleRows > 0 || rows.length === 0);
            }
        }

        function resetCourseFilters() {
            const searchInput = document.getElementById('course-search-input');
            if (searchInput) {
                searchInput.value = '';
            }
            setStatusFilter('all');
            searchInput?.focus();
        }

        function switchViewMode(mode) {
            const gridView = document.getElementById('courses-grid-view');
            const tableView = document.getElementById('courses-table-view');
            const gridBtn = document.getElementById('view-grid-btn');
            const tableBtn = document.getElementById('view-table-btn');

            if (mode === 'grid') {
                gridView.classList.remove('hidden');
                tableView.classList.add('hidden');
                gridBtn.className = 'p-1 rounded text-slate-900 bg-white shadow-2xs transition-all cursor-pointer';
                gridBtn.setAttribute('aria-pressed', 'true');
                tableBtn.className = 'p-1 rounded text-slate-600 hover:text-slate-900 transition-all cursor-pointer';
                tableBtn.setAttribute('aria-pressed', 'false');
            } else {
                gridView.classList.add('hidden');
                tableView.classList.remove('hidden');
                tableBtn.className = 'p-1 rounded text-slate-900 bg-white shadow-2xs transition-all cursor-pointer';
                tableBtn.setAttribute('aria-pressed', 'true');
                gridBtn.className = 'p-1 rounded text-slate-600 hover:text-slate-900 transition-all cursor-pointer';
                gridBtn.setAttribute('aria-pressed', 'false');
            }
        }
    </script>

</x-layout>