<x-layout title="Edit: {{ $user->name }}">

    {{-- Breadcrumb Navigasi --}}
    <div class="mb-6">
        <a href="{{ route('users.index') }}" class="btn-outline text-xs py-2 w-fit inline-flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-600" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            <span>Kembali ke Manajemen Pengguna</span>
        </a>
    </div>

    {{-- Form Container --}}
    <div class="max-w-2xl mx-auto">
        <div class="lms-card p-6 sm:p-8">
            
            {{-- Form Header --}}
            <div class="border-b border-slate-100 pb-5 mb-6">
                <div class="flex items-center gap-3 mb-1.5">
                    <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-800 flex items-center justify-center font-bold" aria-hidden="true">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                        </svg>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                        Edit Data Pengguna
                    </h1>
                </div>
                <p class="text-xs text-slate-600 font-normal">
                    Perbarui profil, penugasan peran (role), atau atur ulang kata sandi pengguna.
                </p>
            </div>

            {{-- Error Summary Alert if any --}}
            @if ($errors->any())
                <div class="p-4 rounded-lg bg-rose-50 border border-rose-200 mb-6" role="alert" aria-labelledby="user-edit-errors">
                    <div class="flex items-center gap-2 text-rose-800 text-xs font-bold mb-1">
                        <svg class="w-4 h-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                        <span id="user-edit-errors">Terdapat beberapa kesalahan pengisian formulir:</span>
                    </div>
                    <ul class="list-disc list-inside text-xs text-rose-800 space-y-0.5 font-normal">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Form Edit Pengguna --}}
            <form action="{{ route('users.update', $user) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                {{-- Nama Lengkap --}}
                <div>
                    <label for="name" class="form-label">
                        Nama Lengkap <span class="text-rose-700" aria-hidden="true">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $user->name) }}"
                        class="form-input {{ $errors->has('name') ? 'border-rose-400 focus:border-rose-500' : '' }}"
                        aria-required="true"
                        aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                        aria-describedby="name-hint @error('name') name-error @enderror"
                        required
                    >
                    <p id="name-hint" class="text-[11px] text-slate-600 mt-1 font-normal">Nama lengkap pengguna beserta gelar (jika dosen).</p>
                    @error('name')
                        <p id="name-error" class="text-xs text-rose-700 mt-1 font-semibold" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="form-label">
                        Alamat Email <span class="text-rose-700" aria-hidden="true">*</span>
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email', $user->email) }}"
                        class="form-input {{ $errors->has('email') ? 'border-rose-400 focus:border-rose-500' : '' }}"
                        aria-required="true"
                        aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                        aria-describedby="email-hint @error('email') email-error @enderror"
                        required
                    >
                    <p id="email-hint" class="text-[11px] text-slate-600 mt-1 font-normal">Alamat email aktif untuk autentikasi dan notifikasi.</p>
                    @error('email')
                        <p id="email-error" class="text-xs text-rose-700 mt-1 font-semibold" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Baris: Peran (Role) & NIM/NIP --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="role" class="form-label">
                            Peran Pengguna (Role) <span class="text-rose-700" aria-hidden="true">*</span>
                        </label>
                        <select
                            id="role"
                            name="role"
                            class="form-input {{ $errors->has('role') ? 'border-rose-400 focus:border-rose-500' : '' }}"
                            aria-required="true"
                            aria-invalid="{{ $errors->has('role') ? 'true' : 'false' }}"
                            aria-describedby="role-hint @error('role') role-error @enderror"
                            required
                        >
                            <option value="mahasiswa" {{ old('role', $user->role) === 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                            <option value="dosen" {{ old('role', $user->role) === 'dosen' ? 'selected' : '' }}>Dosen</option>
                            <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Administrator</option>
                        </select>
                        <p id="role-hint" class="text-[11px] text-slate-600 mt-1 font-normal">Tingkat hak akses di dalam sistem.</p>
                        @error('role')
                            <p id="role-error" class="text-xs text-rose-700 mt-1 font-semibold" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="nim_nip" class="form-label">
                            NIM / NIP
                        </label>
                        <input
                            type="text"
                            id="nim_nip"
                            name="nim_nip"
                            value="{{ old('nim_nip', $user->nim_nip) }}"
                            maxlength="20"
                            class="form-input font-mono {{ $errors->has('nim_nip') ? 'border-rose-400 focus:border-rose-500' : '' }}"
                            aria-invalid="{{ $errors->has('nim_nip') ? 'true' : 'false' }}"
                            aria-describedby="nim_nip-hint @error('nim_nip') nim_nip-error @enderror"
                        >
                        <p id="nim_nip-hint" class="text-[11px] text-slate-600 mt-1 font-normal">Nomor Induk Mahasiswa atau Pegawai/Dosen.</p>
                        @error('nim_nip')
                            <p id="nim_nip-error" class="text-xs text-rose-700 mt-1 font-semibold" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Password (Opsional saat edit) --}}
                <div>
                    <label for="password" class="form-label">
                        Kata Sandi Baru (Kosongkan jika tidak ingin mengubah)
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Minimal 8 karakter jika diisi"
                        minlength="8"
                        class="form-input {{ $errors->has('password') ? 'border-rose-400 focus:border-rose-500' : '' }}"
                        aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                        aria-describedby="password-hint @error('password') password-error @enderror"
                    >
                    <p id="password-hint" class="text-[11px] text-slate-600 mt-1 font-normal">Hanya isi bidang ini jika ingin memperbarui kata sandi akun.</p>
                    @error('password')
                        <p id="password-error" class="text-xs text-rose-700 mt-1 font-semibold" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tombol Aksi Form --}}
                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="{{ route('users.index') }}" class="btn-outline">
                        Batal
                    </a>
                    <button type="submit" class="btn-primary">
                        <svg class="w-4 h-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        <span>Perbarui Pengguna</span>
                    </button>
                </div>

            </form>

        </div>
    </div>

</x-layout>
