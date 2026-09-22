<x-layout title="Manajemen Pengguna">

    {{-- Header Halaman & Aksi Tambah --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                Manajemen Pengguna
            </h1>
            <p class="text-xs text-slate-600 mt-0.5 font-normal">
                Kelola akun administrator, dosen pengampu, dan mahasiswa terdaftar di KampusLMS.
            </p>
        </div>

        <a href="{{ route('users.create') }}" class="btn-primary flex-shrink-0 text-xs py-2">
            <svg class="w-3.5 h-3.5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
            </svg>
            <span>Tambah Pengguna</span>
        </a>
    </div>

    {{-- Search & Filter Controls Bar --}}
    <div class="lms-card p-3 mb-6 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
        <form method="GET" action="{{ route('users.index') }}" class="relative flex-1">
            @if(request('role'))
                <input type="hidden" name="role" value="{{ request('role') }}">
            @endif
            <svg class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-slate-500" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <input 
                type="text" 
                name="q"
                value="{{ request('q') }}"
                id="user-search-input" 
                placeholder="Cari nama, email, atau NIM/NIP pengguna..." 
                aria-label="Cari nama, email, atau NIM/NIP pengguna"
                class="w-full pl-8.5 pr-12 py-1.5 rounded-lg bg-slate-50 border border-slate-200 text-xs text-slate-900 placeholder:text-slate-500 focus:outline-none focus:border-slate-900 focus:bg-white transition-colors"
            >
            <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" aria-hidden="true">
                <kbd class="hidden sm:inline-block px-1.5 py-0.5 text-[10px] font-mono text-slate-600 bg-white border border-slate-200 rounded">↵</kbd>
            </div>
        </form>

        <div class="flex items-center gap-1" id="role-filter-buttons" role="group" aria-label="Filter berdasarkan peran pengguna">
            @php $curRole = request('role'); @endphp
            <a href="{{ route('users.index', array_filter(['q' => request('q')])) }}" 
               class="role-filter-btn px-2.5 py-1.5 rounded-md text-xs font-semibold transition-all {{ empty($curRole) ? 'bg-[#111111] text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Semua
            </a>
            <a href="{{ route('users.index', array_filter(['q' => request('q'), 'role' => 'mahasiswa'])) }}" 
               class="role-filter-btn px-2.5 py-1.5 rounded-md text-xs font-semibold transition-all {{ $curRole === 'mahasiswa' ? 'bg-[#111111] text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Mahasiswa
            </a>
            <a href="{{ route('users.index', array_filter(['q' => request('q'), 'role' => 'dosen'])) }}" 
               class="role-filter-btn px-2.5 py-1.5 rounded-md text-xs font-semibold transition-all {{ $curRole === 'dosen' ? 'bg-[#111111] text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Dosen
            </a>
            <a href="{{ route('users.index', array_filter(['q' => request('q'), 'role' => 'admin'])) }}" 
               class="role-filter-btn px-2.5 py-1.5 rounded-md text-xs font-semibold transition-all {{ $curRole === 'admin' ? 'bg-[#111111] text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Admin
            </a>
        </div>
    </div>

    {{-- Tabel Pengguna Modern Utilitarian --}}
    <div class="lms-card overflow-hidden mb-6">
        <div class="px-5 py-3 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                Daftar Akun Pengguna
            </h3>
            <span class="text-xs text-slate-600 font-mono">Halaman {{ $users->currentPage() }} dari {{ $users->lastPage() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs" id="users-table" aria-label="Tabel daftar akun pengguna">
                <thead class="bg-slate-50/50 text-[11px] font-bold text-slate-700 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th scope="col" class="px-5 py-2.5">Pengguna</th>
                        <th scope="col" class="px-5 py-2.5">Role</th>
                        <th scope="col" class="px-5 py-2.5">NIM / NIP</th>
                        <th scope="col" class="px-5 py-2.5">Terdaftar</th>
                        <th scope="col" class="px-5 py-2.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700" id="users-tbody">
                    @forelse ($users as $user)
                        <tr class="user-row hover:bg-slate-50 transition-colors" data-role="{{ strtolower($user->role) }}">
                            
                            {{-- Kolom Pengguna (Avatar + Nama + Email) --}}
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded bg-slate-100 border border-slate-200 text-slate-800 font-bold text-[11px] flex items-center justify-center flex-shrink-0 font-mono" aria-hidden="true">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <a href="{{ route('users.show', $user) }}" class="font-semibold text-slate-900 hover:text-slate-700 block truncate focus:outline-none focus:underline">
                                            {{ $user->name }}
                                        </a>
                                        <p class="text-[11px] text-slate-600 font-mono truncate">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Kolom Role --}}
                            <td class="px-5 py-3">
                                @if ($user->role === 'admin')
                                    <span class="badge-coral text-[11px]">Admin</span>
                                @elseif ($user->role === 'dosen')
                                    <span class="badge-sky text-[11px]">Dosen</span>
                                @else
                                    <span class="badge-mint text-[11px]">Mahasiswa</span>
                                @endif
                            </td>

                            {{-- Kolom NIM / NIP --}}
                            <td class="px-5 py-3 font-mono text-[11px] text-slate-800">
                                {{ $user->nim_nip ?? '-' }}
                            </td>

                            {{-- Kolom Tanggal Dibuat --}}
                            <td class="px-5 py-3 text-slate-600 font-mono text-[11px]">
                                {{ $user->created_at->format('d M Y') }}
                            </td>

                            {{-- Kolom Aksi --}}
                            <td class="px-5 py-3 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <a href="{{ route('users.show', $user) }}" class="px-2 py-1 rounded text-xs font-semibold text-slate-800 hover:text-slate-950 hover:bg-slate-100 transition-colors" aria-label="Lihat profil {{ $user->name }}">
                                        Lihat
                                    </a>
                                    <span class="text-slate-300" aria-hidden="true">•</span>
                                    <a href="{{ route('users.edit', $user) }}" class="px-2 py-1 rounded text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors" aria-label="Edit akun {{ $user->name }}">
                                        Edit
                                    </a>
                                    <span class="text-slate-300" aria-hidden="true">•</span>
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna {{ $user->name }}?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2 py-1 rounded text-xs font-medium text-rose-700 hover:text-rose-900 hover:bg-rose-50 transition-colors cursor-pointer" aria-label="Hapus akun {{ $user->name }}">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-slate-600 text-xs font-normal">
                                Belum ada akun pengguna terdaftar.
                            </td>
                        </tr>
                    @endforelse

                    {{-- Zero-Result Search State for Users Table --}}
                    <tr id="users-zero-state" class="hidden" role="status" aria-live="polite">
                        <td colspan="5" class="px-5 py-8 text-center text-slate-600">
                            <p class="font-bold text-slate-800 mb-1">Tidak ada pengguna yang cocok</p>
                            <p class="text-xs text-slate-600 mb-3 font-normal">Tidak ditemukan akun pengguna yang sesuai kata kunci pencarian atau filter perannya.</p>
                            <button type="button" onclick="resetUserFilters()" class="btn-outline text-xs inline-flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                </svg>
                                <span>Reset Filter & Pencarian</span>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Client Filter Script --}}
    <script>
        let currentRole = 'all';

        function setRoleFilter(role) {
            currentRole = role;
            document.querySelectorAll('.role-filter-btn').forEach(btn => {
                const isCurrent = (btn.dataset.role === role);
                btn.setAttribute('aria-pressed', isCurrent ? 'true' : 'false');
                if (isCurrent) {
                    btn.className = 'role-filter-btn px-2.5 py-1.5 rounded-md text-xs font-semibold transition-all bg-[#111111] text-white cursor-pointer';
                } else {
                    btn.className = 'role-filter-btn px-2.5 py-1.5 rounded-md text-xs font-semibold transition-all bg-slate-100 text-slate-700 hover:bg-slate-200 cursor-pointer';
                }
            });
            filterUserTable();
        }

        function filterUserTable() {
            const query = (document.getElementById('user-search-input')?.value || '').toLowerCase().trim();
            const rows = document.querySelectorAll('#users-tbody .user-row');
            let visibleRows = 0;
            
            rows.forEach(row => {
                const rowRole = row.dataset.role || '';
                const text = row.innerText.toLowerCase();
                const matchesRole = (currentRole === 'all' || rowRole === currentRole);
                const matchesQuery = (query === '' || text.includes(query));
                const isVisible = (matchesRole && matchesQuery);

                row.style.display = isVisible ? '' : 'none';
                if (isVisible) visibleRows++;
            });

            const zeroState = document.getElementById('users-zero-state');
            if (zeroState) {
                zeroState.classList.toggle('hidden', visibleRows > 0 || rows.length === 0);
            }
        }

        function resetUserFilters() {
            const searchInput = document.getElementById('user-search-input');
            if (searchInput) {
                searchInput.value = '';
            }
            setRoleFilter('all');
            searchInput?.focus();
        }
    </script>

    {{-- Pagination Links --}}
    <div class="mt-4">
        {{ $users->links() }}
    </div>

</x-layout>
