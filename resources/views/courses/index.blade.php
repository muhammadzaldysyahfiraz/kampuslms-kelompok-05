<x-layout title="Daftar Mata Kuliah"> {{-- Membuka layout utama dengan judul halaman Daftar Mata Kuliah. --}}

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6"> {{-- Membuat header halaman dengan layout responsif. --}}
        <div> {{-- Membuka area judul dan deskripsi halaman. --}}
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight"> {{-- Membuka judul utama halaman. --}}
                {{ $activeRole === 'dosen' ? 'Mata Kuliah Diampu' : 'Daftar Mata Kuliah' }} {{-- Menampilkan judul sesuai role aktif. --}}
            </h1> {{-- Menutup judul utama. --}}

            <p class="text-xs text-slate-600 mt-0.5"> {{-- Membuka deskripsi halaman. --}}
                @if ($activeRole === 'mahasiswa') {{-- Memeriksa apakah role aktif adalah mahasiswa. --}}
                    Jelajahi silabus perkuliahan, unduh modul bahan ajar, dan pantau tugas pada kelas Anda. {{-- Menampilkan deskripsi untuk mahasiswa. --}}
                @elseif ($activeRole === 'dosen') {{-- Memeriksa apakah role aktif adalah dosen. --}}
                    Kelola materi pembelajaran, silabus, dan penugasan kelas yang Anda ampu semester ini. {{-- Menampilkan deskripsi untuk dosen. --}}
                @else {{-- Menangani role selain mahasiswa dan dosen. --}}
                    Kelola kurikulum pembelajaran, dosen pengampu, dan status perkuliahan di sistem KampusLMS. {{-- Menampilkan deskripsi untuk admin atau role lain. --}}
                @endif {{-- Mengakhiri kondisi role. --}}
            </p> {{-- Menutup deskripsi halaman. --}}
        </div> {{-- Menutup area judul. --}}

        @if ($activeRole !== 'mahasiswa') {{-- Hanya menampilkan tombol tambah untuk selain mahasiswa. --}}
            <a href="{{ route('courses.create') }}" class="btn-primary flex-shrink-0 text-xs py-2"> {{-- Membuat link menuju form tambah course. --}}
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true"> {{-- Membuka ikon tambah. --}}
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /> {{-- Membentuk simbol plus. --}}
                </svg> {{-- Menutup ikon tambah. --}}
                <span>Tambah Mata Kuliah</span> {{-- Menampilkan teks tombol. --}}
            </a> {{-- Menutup link tambah course. --}}
        @else {{-- Menangani role mahasiswa. --}}
            <div class="flex items-center gap-2 flex-shrink-0"> {{-- Membuka area badge mahasiswa. --}}
                <span class="badge-mint text-xs py-1 px-2.5 font-medium">🎓 Mahasiswa Terdaftar</span> {{-- Menampilkan status mahasiswa terdaftar. --}}
            </div> {{-- Menutup area badge. --}}
        @endif {{-- Mengakhiri kondisi role tombol tambah. --}}
    </div> {{-- Menutup header halaman. --}}

    <div class="lms-card p-3 mb-6 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3"> {{-- Membuka area search dan filter. --}}
        <form action="{{ route('courses.index') }}" method="GET" class="relative flex-1"> {{-- Mengirim pencarian menggunakan GET sehingga state berada di query string. --}}

            <svg class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-slate-500" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"> {{-- Membuka ikon pencarian. --}}
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /> {{-- Membentuk ikon kaca pembesar. --}}
            </svg> {{-- Menutup ikon pencarian. --}}

            <input
                type="text"
                name="q"
                value="{{ request('q') }}"
                placeholder="Cari kode atau nama mata kuliah..."
                aria-label="Cari kode atau nama mata kuliah"
                class="w-full pl-8.5 pr-16 py-1.5 rounded-lg bg-slate-50 border border-slate-200 text-xs text-slate-900 placeholder:text-slate-500 focus:outline-none focus:border-slate-900 focus:bg-white transition-colors"
            > {{-- Menyediakan input pencarian yang mengambil nilai q dari query string. --}}

            <input
                type="hidden"
                name="status"
                value="{{ request('status', 'all') }}"
            > {{-- Mempertahankan filter status ketika pengguna melakukan pencarian baru. --}}

            <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 px-2.5 py-1 rounded-md bg-[#111111] text-white text-[11px] font-semibold">Cari</button> {{-- Mengirim form pencarian ke server. --}}
        </form> {{-- Menutup form pencarian. --}}

        <div class="flex items-center justify-between sm:justify-end gap-2"> {{-- Membuka area filter status dan mode tampilan. --}}

            <div class="flex items-center gap-1" role="group" aria-label="Filter status mata kuliah"> {{-- Membuka kelompok tombol filter status. --}}

                <a href="{{ route('courses.index', ['q' => request('q'), 'status' => 'all']) }}" class="px-2.5 py-1.5 rounded-md text-xs font-semibold transition-all {{ request('status', 'all') === 'all' ? 'bg-[#111111] text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"> {{-- Membuat filter semua status dan mempertahankan q. --}}
                    Semua ({{ $totalCourseCount }}) {{-- Menampilkan jumlah seluruh course. --}}
                </a> {{-- Menutup link filter semua. --}}

                <a href="{{ route('courses.index', ['q' => request('q'), 'status' => 'active']) }}" class="px-2.5 py-1.5 rounded-md text-xs font-semibold transition-all {{ request('status') === 'active' ? 'bg-[#111111] text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"> {{-- Membuat filter status active sambil mempertahankan q. --}}
                    Aktif ({{ $courseCounts->get('active', 0) }}) {{-- Menampilkan jumlah course aktif dari seluruh data. --}}
                </a> {{-- Menutup link filter active. --}}

                <a href="{{ route('courses.index', ['q' => request('q'), 'status' => 'draft']) }}" class="px-2.5 py-1.5 rounded-md text-xs font-semibold transition-all {{ request('status') === 'draft' ? 'bg-[#111111] text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"> {{-- Membuat filter status draft sambil mempertahankan q. --}}
                    Draft ({{ $courseCounts->get('draft', 0) }}) {{-- Menampilkan jumlah course draft dari seluruh data. --}}
                </a> {{-- Menutup link filter draft. --}}

                <a href="{{ route('courses.index', ['q' => request('q'), 'status' => 'archived']) }}" class="px-2.5 py-1.5 rounded-md text-xs font-semibold transition-all {{ request('status') === 'archived' ? 'bg-[#111111] text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"> {{-- Membuat filter status archived sambil mempertahankan q. --}}
                    Arsip ({{ $courseCounts->get('archived', 0) }}) {{-- Menampilkan jumlah course archived dari seluruh data. --}}
                </a> {{-- Menutup link filter archived. --}}

            </div> {{-- Menutup kelompok filter status. --}}

            <div class="flex items-center bg-slate-100 p-0.5 rounded-md border border-slate-200" role="group" aria-label="Pilihan tampilan data"> {{-- Membuka pilihan grid atau tabel. --}}

                <button type="button" onclick="switchViewMode('grid')" id="view-grid-btn" class="p-1 rounded text-slate-900 bg-white shadow-2xs transition-all cursor-pointer" title="Tampilan Kartu Grid" aria-label="Tampilan Kartu Grid" aria-pressed="true"> {{-- Tombol untuk menampilkan mode grid. --}}
                    <svg class="w-3.5 h-3.5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"> {{-- Membuka ikon grid. --}}
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /> {{-- Membentuk ikon grid. --}}
                    </svg> {{-- Menutup ikon grid. --}}
                </button> {{-- Menutup tombol grid. --}}

                <button type="button" onclick="switchViewMode('table')" id="view-table-btn" class="p-1 rounded text-slate-600 hover:text-slate-900 transition-all cursor-pointer" title="Tampilan Tabel Data" aria-label="Tampilan Tabel Data" aria-pressed="false"> {{-- Tombol untuk menampilkan mode tabel. --}}
                    <svg class="w-3.5 h-3.5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"> {{-- Membuka ikon tabel. --}}
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /> {{-- Membentuk ikon tabel. --}}
                    </svg> {{-- Menutup ikon tabel. --}}
                </button> {{-- Menutup tombol tabel. --}}

            </div> {{-- Menutup pilihan mode tampilan. --}}
        </div> {{-- Menutup area filter dan mode tampilan. --}}
    </div> {{-- Menutup area search dan filter. --}}

    <div id="courses-grid-view"> {{-- Membuka tampilan grid course. --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mb-8"> {{-- Membuat grid responsif untuk kartu course. --}}

            @forelse ($courses as $course) {{-- Mengulangi data course yang sudah difilter dan dipaginasi dari controller. --}}
                <div class="course-card lms-card lms-card-hover flex flex-col justify-between p-5 group"> {{-- Membuka kartu mata kuliah. --}}

                    <div> {{-- Membuka area isi utama kartu. --}}
                        <div class="flex items-center justify-between gap-2 mb-2.5"> {{-- Membuka bagian kode, SKS, dan status. --}}

                            <div class="flex items-center gap-1.5"> {{-- Mengelompokkan kode dan SKS. --}}
                                <span class="font-mono text-[11px] font-semibold text-slate-800 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">{{ $course->code }}</span> {{-- Menampilkan kode mata kuliah. --}}
                                <span class="font-mono text-[11px] font-medium text-slate-600">{{ $course->sks }} SKS</span> {{-- Menampilkan jumlah SKS. --}}
                            </div> {{-- Menutup kelompok kode dan SKS. --}}

                            @if ($course->status === 'active') {{-- Memeriksa apakah status course aktif. --}}
                                <span class="badge-mint text-[11px]">Aktif</span> {{-- Menampilkan badge aktif. --}}
                            @elseif ($course->status === 'draft') {{-- Memeriksa apakah status course draft. --}}
                                <span class="badge-amber text-[11px]">Draft</span> {{-- Menampilkan badge draft. --}}
                            @else {{-- Menangani status archived. --}}
                                <span class="badge-dark text-[11px]">Diarsipkan</span> {{-- Menampilkan badge archived. --}}
                            @endif {{-- Mengakhiri kondisi status. --}}

                        </div> {{-- Menutup bagian header kartu. --}}

                        <h2 class="text-sm font-bold text-slate-900 group-hover:text-slate-700 transition-colors line-clamp-1 mb-1"> {{-- Membuka judul mata kuliah. --}}
                            <a href="{{ route('courses.show', $course) }}" class="focus:outline-none focus:underline">{{ $course->name }}</a> {{-- Menghubungkan nama course ke halaman detail. --}}
                        </h2> {{-- Menutup judul mata kuliah. --}}

                        <p class="text-xs text-slate-600 line-clamp-2 mb-4 leading-relaxed font-normal"> {{-- Membuka deskripsi course. --}}
                            {{ $course->description ?? 'Kurikulum pembelajaran terpadu untuk program studi sistem informasi.' }} {{-- Menampilkan deskripsi atau teks default jika kosong. --}}
                        </p> {{-- Menutup deskripsi. --}}
                    </div> {{-- Menutup area isi utama kartu. --}}

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs"> {{-- Membuka footer kartu. --}}

                        <div class="min-w-0 pr-2"> {{-- Membuka area informasi dosen. --}}
                            <p class="font-semibold text-slate-800 truncate text-[11px]">{{ $course->lecturer->name ?? 'Belum Ditugaskan' }}</p> {{-- Menampilkan nama dosen dari relasi lecturer yang sudah eager loaded. --}}
                            <p class="text-[11px] text-slate-600">Dosen Pengampu</p> {{-- Menampilkan label dosen. --}}
                        </div> {{-- Menutup informasi dosen. --}}

                        <div class="flex items-center gap-1.5 flex-shrink-0"> {{-- Membuka tombol aksi course. --}}

                            @if ($activeRole === 'mahasiswa') {{-- Memeriksa apakah pengguna adalah mahasiswa. --}}
                                <a href="{{ route('courses.show', $course) }}" class="px-2.5 py-1 rounded text-xs font-semibold text-slate-800 hover:text-slate-950 hover:bg-slate-100 transition-colors inline-flex items-center gap-1" aria-label="Buka materi kuliah {{ $course->name }}"> {{-- Membuka detail untuk mahasiswa. --}}
                                    <span>Buka</span> {{-- Menampilkan teks aksi buka. --}}
                                    <span aria-hidden="true">→</span> {{-- Menampilkan simbol panah. --}}
                                </a> {{-- Menutup link buka. --}}
                            @else {{-- Menangani dosen atau admin. --}}
                                <a href="{{ route('courses.show', $course) }}" class="px-2 py-1 rounded text-xs font-semibold text-slate-800 hover:text-slate-950 hover:bg-slate-100 transition-colors" aria-label="Buka detail mata kuliah {{ $course->name }}">Buka</a> {{-- Membuka detail course. --}}
                                <span class="text-slate-300" aria-hidden="true">•</span> {{-- Menampilkan pemisah aksi. --}}
                                <a href="{{ route('courses.edit', $course) }}" class="px-2 py-1 rounded text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors" aria-label="Edit mata kuliah {{ $course->name }}">Edit</a> {{-- Membuka form edit course. --}}
                            @endif {{-- Mengakhiri kondisi role aksi. --}}

                        </div> {{-- Menutup tombol aksi. --}}
                    </div> {{-- Menutup footer kartu. --}}

                </div> {{-- Menutup kartu course. --}}

            @empty {{-- Menangani kondisi ketika hasil query tidak memiliki data. --}}
                <div class="col-span-full lms-card p-8 text-center"> {{-- Membuka pesan kosong. --}}
                    @if (request()->filled('q') || request('status', 'all') !== 'all') {{-- Memeriksa apakah kosong karena search atau filter. --}}
                        <p class="text-xs text-slate-600 mb-3">Tidak ada mata kuliah yang sesuai dengan pencarian atau filter.</p> {{-- Menampilkan pesan hasil filter kosong. --}}
                        <a href="{{ route('courses.index') }}" class="btn-outline text-xs">Reset Pencarian & Filter</a> {{-- Menyediakan tombol untuk menghapus state query string. --}}
                    @else {{-- Menangani kondisi database memang belum memiliki course. --}}
                        <p class="text-xs text-slate-600 mb-3">Belum ada mata kuliah yang terdaftar.</p> {{-- Menampilkan pesan bahwa belum ada course. --}}
                        @if ($activeRole !== 'mahasiswa') {{-- Memeriksa apakah pengguna boleh menambah course. --}}
                            <a href="{{ route('courses.create') }}" class="btn-primary text-xs">Tambah Mata Kuliah Baru</a> {{-- Menyediakan tombol tambah course. --}}
                        @endif {{-- Mengakhiri kondisi tombol tambah. --}}
                    @endif {{-- Mengakhiri kondisi pesan kosong. --}}
                </div> {{-- Menutup pesan kosong. --}}
            @endforelse {{-- Mengakhiri perulangan course. --}}

        </div> {{-- Menutup grid course. --}}
    </div> {{-- Menutup tampilan grid. --}}

    <div id="courses-table-view" class="hidden"> {{-- Membuka tampilan tabel dan menyembunyikannya secara default. --}}
        <div class="lms-card overflow-hidden mb-6"> {{-- Membuka container tabel. --}}

            <div class="px-5 py-3 bg-slate-50 border-b border-slate-200 flex items-center justify-between"> {{-- Membuka header tabel. --}}
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Tabel Mata Kuliah</h3> {{-- Menampilkan judul tabel. --}}
                <span class="text-xs text-slate-600 font-mono">{{ $courses->count() }} Data Halaman Ini</span> {{-- Menampilkan jumlah course yang sedang ada di halaman aktif. --}}
            </div> {{-- Menutup header tabel. --}}

            <div class="overflow-x-auto"> {{-- Membuat tabel dapat di-scroll secara horizontal pada layar kecil. --}}
                <table class="w-full text-left text-xs" id="courses-table" aria-label="Tabel daftar mata kuliah"> {{-- Membuka tabel course. --}}

                    <thead class="bg-slate-50/50 text-[11px] font-bold text-slate-700 uppercase tracking-wider border-b border-slate-200"> {{-- Membuka kepala tabel. --}}
                        <tr> {{-- Membuka baris header. --}}
                            <th scope="col" class="px-5 py-2.5">Kode</th> {{-- Menampilkan kolom kode. --}}
                            <th scope="col" class="px-5 py-2.5">Nama Mata Kuliah</th> {{-- Menampilkan kolom nama. --}}
                            <th scope="col" class="px-5 py-2.5">SKS</th> {{-- Menampilkan kolom SKS. --}}
                            <th scope="col" class="px-5 py-2.5">Dosen Pengampu</th> {{-- Menampilkan kolom dosen. --}}
                            <th scope="col" class="px-5 py-2.5">Status</th> {{-- Menampilkan kolom status. --}}
                            <th scope="col" class="px-5 py-2.5 text-right">Aksi</th> {{-- Menampilkan kolom aksi. --}}
                        </tr> {{-- Menutup baris header. --}}
                    </thead> {{-- Menutup kepala tabel. --}}

                    <tbody class="divide-y divide-slate-100 text-slate-700"> {{-- Membuka isi tabel. --}}

                        @forelse ($courses as $course) {{-- Mengulangi course hasil query server-side. --}}
                            <tr class="hover:bg-slate-50 transition-colors"> {{-- Membuka satu baris course. --}}

                                <td class="px-5 py-3 font-mono font-semibold text-slate-900">{{ $course->code }}</td> {{-- Menampilkan kode course. --}}

                                <td class="px-5 py-3 font-semibold text-slate-900"> {{-- Membuka kolom nama course. --}}
                                    <a href="{{ route('courses.show', $course) }}" class="hover:underline focus:outline-none focus:underline">{{ $course->name }}</a> {{-- Menghubungkan nama course ke halaman detail. --}}
                                </td> {{-- Menutup kolom nama. --}}

                                <td class="px-5 py-3 font-mono text-slate-600">{{ $course->sks }} SKS</td> {{-- Menampilkan jumlah SKS. --}}

                                <td class="px-5 py-3 text-slate-700">{{ $course->lecturer->name ?? 'Belum Ditugaskan' }}</td> {{-- Menampilkan nama dosen dari relasi lecturer. --}}

                                <td class="px-5 py-3"> {{-- Membuka kolom status. --}}
                                    @if ($course->status === 'active') {{-- Memeriksa status active. --}}
                                        <span class="badge-mint text-[11px]">Aktif</span> {{-- Menampilkan badge active. --}}
                                    @elseif ($course->status === 'draft') {{-- Memeriksa status draft. --}}
                                        <span class="badge-amber text-[11px]">Draft</span> {{-- Menampilkan badge draft. --}}
                                    @else {{-- Menangani status archived. --}}
                                        <span class="badge-dark text-[11px]">Diarsipkan</span> {{-- Menampilkan badge archived. --}}
                                    @endif {{-- Mengakhiri kondisi status. --}}
                                </td> {{-- Menutup kolom status. --}}

                                <td class="px-5 py-3 text-right"> {{-- Membuka kolom aksi. --}}
                                    <div class="inline-flex items-center gap-1"> {{-- Membuka kelompok tombol aksi. --}}

                                        <a href="{{ route('courses.show', $course) }}" class="px-2 py-1 rounded text-xs font-semibold text-slate-800 hover:text-slate-950 hover:bg-slate-100 transition-colors" aria-label="Lihat detail {{ $course->name }}">{{ $activeRole === 'mahasiswa' ? 'Buka' : 'Lihat' }}</a> {{-- Membuka detail course. --}}

                                        @if ($activeRole !== 'mahasiswa') {{-- Hanya admin atau dosen yang mendapatkan aksi edit dan hapus. --}}
                                            <span class="text-slate-300" aria-hidden="true">•</span> {{-- Menampilkan pemisah aksi. --}}
                                            <a href="{{ route('courses.edit', $course) }}" class="px-2 py-1 rounded text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors" aria-label="Edit {{ $course->name }}">Edit</a> {{-- Membuka form edit. --}}
                                            <span class="text-slate-300" aria-hidden="true">•</span> {{-- Menampilkan pemisah aksi. --}}

                                            <form action="{{ route('courses.destroy', $course) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus mata kuliah {{ $course->name }}?')" class="inline"> {{-- Membuat form DELETE untuk menghapus course. --}}
                                                @csrf {{-- Menambahkan token CSRF untuk melindungi form. --}}
                                                @method('DELETE') {{-- Mengubah method POST HTML menjadi DELETE untuk resource route Laravel. --}}
                                                <button type="submit" class="px-2 py-1 rounded text-xs font-medium text-rose-700 hover:text-rose-900 hover:bg-rose-50 transition-colors cursor-pointer" aria-label="Hapus mata kuliah {{ $course->name }}">Hapus</button> {{-- Menjalankan penghapusan setelah konfirmasi. --}}
                                            </form> {{-- Menutup form hapus. --}}

                                        @endif {{-- Mengakhiri kondisi role aksi. --}}
                                    </div> {{-- Menutup kelompok aksi. --}}
                                </td> {{-- Menutup kolom aksi. --}}

                            </tr> {{-- Menutup satu baris course. --}}

                        @empty {{-- Menangani tabel tanpa hasil. --}}
                            <tr> {{-- Membuka baris pesan kosong. --}}
                                <td colspan="6" class="px-5 py-8 text-center text-slate-600"> {{-- Menggabungkan seluruh kolom untuk pesan kosong. --}}

                                    @if (request()->filled('q') || request('status', 'all') !== 'all') {{-- Memeriksa apakah kosong karena pencarian atau filter. --}}
                                        <p class="font-bold text-slate-800 mb-1">Tidak ada mata kuliah yang cocok</p> {{-- Menampilkan judul hasil kosong. --}}
                                        <p class="text-xs text-slate-600 mb-3">Tidak ditemukan hasil sesuai kata kunci atau filter status.</p> {{-- Menjelaskan bahwa filter tidak menemukan hasil. --}}
                                        <a href="{{ route('courses.index') }}" class="btn-outline text-xs">Reset Filter & Pencarian</a> {{-- Menyediakan tombol reset. --}}
                                    @else {{-- Menangani kondisi database memang kosong. --}}
                                        <p class="font-bold text-slate-800 mb-1">Belum ada mata kuliah</p> {{-- Menampilkan pesan database kosong. --}}
                                    @endif {{-- Mengakhiri kondisi hasil kosong. --}}

                                </td> {{-- Menutup sel pesan kosong. --}}
                            </tr> {{-- Menutup baris pesan kosong. --}}
                        @endforelse {{-- Mengakhiri perulangan tabel. --}}

                    </tbody> {{-- Menutup isi tabel. --}}
                </table> {{-- Menutup tabel. --}}
            </div> {{-- Menutup wrapper tabel. --}}
        </div> {{-- Menutup container tabel. --}}
    </div> {{-- Menutup tampilan tabel. --}}

    @if ($courses->hasPages()) {{-- Menampilkan pagination hanya jika jumlah data membutuhkan lebih dari satu halaman. --}}
        <div class="mt-6 flex justify-center"> {{-- Membuat container pagination berada di tengah. --}}
            {{ $courses->links() }} {{-- Menampilkan tombol pagination Laravel dengan query string q dan status yang dipertahankan oleh withQueryString(). --}}
        </div> {{-- Menutup container pagination. --}}
    @endif {{-- Mengakhiri kondisi pagination. --}}

    <script> // Membuka JavaScript yang hanya diperlukan untuk pergantian tampilan grid dan tabel.
        function switchViewMode(mode) { // Membuat fungsi untuk berganti antara tampilan grid dan tabel.
            const gridView = document.getElementById('courses-grid-view'); // Mengambil elemen tampilan grid.
            const tableView = document.getElementById('courses-table-view'); // Mengambil elemen tampilan tabel.
            const gridBtn = document.getElementById('view-grid-btn'); // Mengambil tombol mode grid.
            const tableBtn = document.getElementById('view-table-btn'); // Mengambil tombol mode tabel.

            if (mode === 'grid') { // Memeriksa apakah pengguna memilih tampilan grid.
                gridView.classList.remove('hidden'); // Menampilkan tampilan grid.
                tableView.classList.add('hidden'); // Menyembunyikan tampilan tabel.
                gridBtn.className = 'p-1 rounded text-slate-900 bg-white shadow-2xs transition-all cursor-pointer'; // Memberi tampilan aktif pada tombol grid.
                gridBtn.setAttribute('aria-pressed', 'true'); // Menandai tombol grid sebagai aktif untuk aksesibilitas.
                tableBtn.className = 'p-1 rounded text-slate-600 hover:text-slate-900 transition-all cursor-pointer'; // Mengembalikan tombol tabel menjadi tidak aktif.
                tableBtn.setAttribute('aria-pressed', 'false'); // Menandai tombol tabel sebagai tidak aktif.
            } else { // Menangani pilihan tampilan tabel.
                gridView.classList.add('hidden'); // Menyembunyikan tampilan grid.
                tableView.classList.remove('hidden'); // Menampilkan tampilan tabel.
                tableBtn.className = 'p-1 rounded text-slate-900 bg-white shadow-2xs transition-all cursor-pointer'; // Memberi tampilan aktif pada tombol tabel.
                tableBtn.setAttribute('aria-pressed', 'true'); // Menandai tombol tabel sebagai aktif.
                gridBtn.className = 'p-1 rounded text-slate-600 hover:text-slate-900 transition-all cursor-pointer'; // Mengembalikan tombol grid menjadi tidak aktif.
                gridBtn.setAttribute('aria-pressed', 'false'); // Menandai tombol grid sebagai tidak aktif.
            } // Mengakhiri percabangan mode tampilan.
        } // Menutup fungsi switchViewMode.
    </script> {{-- Menutup JavaScript. --}}

</x-layout> {{-- Menutup layout utama. --}}