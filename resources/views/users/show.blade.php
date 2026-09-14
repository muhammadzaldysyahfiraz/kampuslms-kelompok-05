<x-layout title="Profil: {{ $user->name }}">

    {{-- Breadcrumb Navigasi & Action Buttons --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <a href="{{ route('users.index') }}" class="btn-outline text-xs py-2 w-fit inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            <span>Kembali ke Manajemen Pengguna</span>
        </a>

        <div class="flex items-center gap-2">
            <a href="{{ route('users.edit', $user) }}" class="btn-secondary text-xs py-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                </svg>
                <span>Edit Profil</span>
            </a>

            <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna {{ $user->name }}?')" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger-soft text-xs py-2 px-3">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                    <span>Hapus</span>
                </button>
            </form>
        </div>
    </div>

    {{-- Detail User Card --}}
    <div class="max-w-3xl mx-auto">
        <div class="lms-card p-6 sm:p-8 overflow-hidden relative">
            
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6 pb-6 border-b border-slate-100">
                <div class="w-20 h-20 rounded-2xl bg-gradient-to-tr {{ $user->role === 'admin' ? 'from-[#FE774C] to-[#FFC152]' : ($user->role === 'dosen' ? 'from-[#66A7F2] to-[#5DD299]' : 'from-slate-300 to-slate-400') }} flex items-center justify-center text-white font-extrabold text-2xl shadow-md">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>

                <div class="text-center sm:text-left flex-1">
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mb-2">
                        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">
                            {{ $user->name }}
                        </h1>
                        @if ($user->role === 'admin')
                            <span class="badge-coral">Administrator</span>
                        @elseif ($user->role === 'dosen')
                            <span class="badge-sky">Dosen</span>
                        @else
                            <span class="badge-mint">Mahasiswa</span>
                        @endif
                    </div>

                    <p class="text-sm font-mono text-slate-500 mb-1">{{ $user->email }}</p>
                    <p class="text-xs text-slate-400">
                        NIM / NIP: <strong class="text-slate-700 font-mono">{{ $user->nim_nip ?? 'Tidak ada' }}</strong>
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-6 text-xs text-slate-600">
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70">
                    <span class="text-slate-400 block mb-1">Status Verifikasi Email</span>
                    <span class="font-bold text-slate-800">
                        {{ $user->email_verified_at ? 'Terverifikasi (' . $user->email_verified_at->format('d M Y') . ')' : 'Belum Diverifikasi' }}
                    </span>
                </div>

                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70">
                    <span class="text-slate-400 block mb-1">Terdaftar Sejak</span>
                    <span class="font-bold text-slate-800 font-mono">
                        {{ $user->created_at->format('d F Y, H:i') }}
                    </span>
                </div>
            </div>

        </div>
    </div>

</x-layout>
