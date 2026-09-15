<x-layout title="Tambah Pengguna">

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
                        👤
                    </div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                        Tambah Pengguna Baru
                    </h1>
                </div>
                <p class="text-xs text-slate-500">
                    Daftarkan akun admin, dosen pengampu, atau mahasiswa ke dalam sistem KampusLMS.
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

            {{-- Form Penambahan Pengguna --}}
            <form action="{{ route('users.store') }}" method="POST" class="space-y-5">
                @csrf

                {{-- Nama Lengkap --}}
                <div>
                    <label for="name" class="form-label">
                        Nama Lengkap <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        placeholder="Contoh: Muhammad Rifa"
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
                        value="{{ old('email') }}"
                        placeholder="user@kampuslms.test"
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
                            <option value="">-- Pilih Peran --</option>
                            <option value="mahasiswa" {{ old('role') === 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                            <option value="dosen" {{ old('role') === 'dosen' ? 'selected' : '' }}>Dosen</option>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Administrator</option>
                        </select>
                        @error('role')
                            <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="nim_nip" class="form-label">
                            NIM / NIP (Opsional)
                        </label>
                        <input
                            type="text"
                            id="nim_nip"
                            name="nim_nip"
                            value="{{ old('nim_nip') }}"
                            placeholder="Contoh: 10241050"
                            class="form-input font-mono {{ $errors->has('nim_nip') ? 'border-rose-400 focus:border-rose-500' : '' }}"
                        >
                        @error('nim_nip')
                            <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="form-label">
                        Kata Sandi (Password) <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Minimal 8 karakter"
                        class="form-input {{ $errors->has('password') ? 'border-rose-400 focus:border-rose-500' : '' }}"
                        required
                    >
                    <p class="text-[11px] text-slate-400 mt-1">Gunakan kombinasi minimal 8 karakter untuk keamanan akun.</p>
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
                        <span>Simpan Pengguna</span>
                    </button>
                </div>

            </form>

        </div>
    </div>

</x-layout>
