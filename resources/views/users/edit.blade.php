<x-layout title="Edit: {{ $user->name }}">

    {{-- Breadcrumb Navigasi --}}
    <div class="mb-6">
        <a href="{{ route('users.index') }}" class="btn-outline text-xs py-2 w-fit inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
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
                    <div class="w-8 h-8 rounded-xl bg-[#66A7F2]/15 text-[#66A7F2] flex items-center justify-center font-bold">
                        ✎
                    </div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                        Edit Data Pengguna
                    </h1>
                </div>
                <p class="text-xs text-slate-500">
                    Perbarui profil, penugasan peran (role), atau atur ulang kata sandi pengguna.
                </p>
            </div>

            {{-- Error Summary Alert if any --}}
            @if ($errors->any())
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 mb-6">
                    <div class="flex items-center gap-2 text-rose-700 text-xs font-bold mb-1">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                        <span>Terdapat beberapa kesalahan pengisian formulir:</span>
                    </div>
                    <ul class="list-disc list-inside text-xs text-rose-600 space-y-0.5">
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
                        Nama Lengkap <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $user->name) }}"
                        class="form-input {{ $errors->has('name') ? 'border-rose-400 focus:border-rose-500' : '' }}"
                        required
                    >
                    @error('name')
                        <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="form-label">
                        Alamat Email <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email', $user->email) }}"
                        class="form-input {{ $errors->has('email') ? 'border-rose-400 focus:border-rose-500' : '' }}"
                        required
                    >
                    @error('email')
                        <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Baris: Peran (Role) & NIM/NIP --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="role" class="form-label">
                            Peran Pengguna (Role) <span class="text-rose-500">*</span>
                        </label>
                        <select
                            id="role"
                            name="role"
                            class="form-input {{ $errors->has('role') ? 'border-rose-400 focus:border-rose-500' : '' }}"
                            required
                        >
                            <option value="mahasiswa" {{ old('role', $user->role) === 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                            <option value="dosen" {{ old('role', $user->role) === 'dosen' ? 'selected' : '' }}>Dosen</option>
                            <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Administrator</option>
                        </select>
                        @error('role')
                            <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
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
                            class="form-input font-mono {{ $errors->has('nim_nip') ? 'border-rose-400 focus:border-rose-500' : '' }}"
                        >
                        @error('nim_nip')
                            <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
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
                        class="form-input {{ $errors->has('password') ? 'border-rose-400 focus:border-rose-500' : '' }}"
                    >
                    @error('password')
                        <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tombol Aksi Form --}}
                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="{{ route('users.index') }}" class="btn-outline">
                        Batal
                    </a>
                    <button type="submit" class="btn-primary">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        <span>Perbarui Pengguna</span>
                    </button>
                </div>

            </form>

        </div>
    </div>

</x-layout>
