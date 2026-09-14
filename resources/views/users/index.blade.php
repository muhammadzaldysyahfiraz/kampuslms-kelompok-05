<x-layout title="Manajemen Pengguna">

    {{-- Header Halaman & Aksi Tambah --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Manajemen Pengguna</h1>
                <span class="badge-sky text-xs">
                    {{ $users->total() }} Pengguna
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-1">
                Kelola akun administrator, dosen pengampu, dan mahasiswa terdaftar di KampusLMS.
            </p>
        </div>

        <a href="{{ route('users.create') }}" class="btn-primary flex-shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
            </svg>
            <span>Tambah Pengguna</span>
        </a>
    </div>

    {{-- Tabel Pengguna Modern (DESIGN.md Section 6.3) --}}
    <div class="lms-card overflow-hidden mb-6">
        <div class="px-6 py-4 bg-slate-50/80 border-b border-slate-200/80 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">
                Daftar Akun Pengguna
            </h3>
            <span class="text-xs text-slate-500">Halaman {{ $users->currentPage() }} dari {{ $users->lastPage() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50/50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Pengguna</th>
                        <th class="px-6 py-3.5">Role</th>
                        <th class="px-6 py-3.5">NIM / NIP</th>
                        <th class="px-6 py-3.5">Terdaftar</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse ($users as $user)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            
                            {{-- Kolom Pengguna (Avatar + Nama + Email) --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-tr {{ $user->role === 'admin' ? 'from-[#FE774C] to-[#FFC152]' : ($user->role === 'dosen' ? 'from-[#66A7F2] to-[#5DD299]' : 'from-slate-300 to-slate-400') }} flex items-center justify-center text-white font-bold text-xs shadow-xs">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('users.show', $user) }}" class="font-bold text-slate-900 hover:text-[#66A7F2] transition-colors block">
                                            {{ $user->name }}
                                        </a>
                                        <p class="text-xs text-slate-400 font-mono">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Kolom Role --}}
                            <td class="px-6 py-4">
                                @if ($user->role === 'admin')
                                    <span class="badge-coral text-[10px]">Admin</span>
                                @elseif ($user->role === 'dosen')
                                    <span class="badge-sky text-[10px]">Dosen</span>
                                @else
                                    <span class="badge-mint text-[10px]">Mahasiswa</span>
                                @endif
                            </td>

                            {{-- Kolom NIM / NIP --}}
                            <td class="px-6 py-4 font-mono text-xs font-bold text-slate-800">
                                {{ $user->nim_nip ?? '-' }}
                            </td>

                            {{-- Kolom Tanggal Dibuat --}}
                            <td class="px-6 py-4 text-xs text-slate-500">
                                {{ $user->created_at->format('d M Y') }}
                            </td>

                            {{-- Kolom Aksi --}}
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('users.show', $user) }}" class="px-2.5 py-1 rounded-md text-xs font-semibold text-[#66A7F2] hover:bg-sky-50 transition-colors">
                                        Lihat
                                    </a>
                                    <a href="{{ route('users.edit', $user) }}" class="px-2.5 py-1 rounded-md text-xs font-semibold text-slate-700 hover:bg-slate-100 transition-colors">
                                        Edit
                                    </a>
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna {{ $user->name }}?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1 rounded-md text-xs font-semibold text-rose-600 hover:bg-rose-50 transition-colors">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-400">
                                Belum ada data pengguna yang terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination Links --}}
    <div class="mt-4">
        {{ $users->links() }}
    </div>

</x-layout>
