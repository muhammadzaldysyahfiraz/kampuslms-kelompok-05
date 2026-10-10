<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — KampusLMS</title>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    {{-- Vite Asset Bundler --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-full font-sans antialiased text-slate-900 bg-slate-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8 selection:bg-slate-900 selection:text-white">

    <div class="sm:mx-auto sm:w-full sm:max-w-md px-4">
        {{-- Brand Logo --}}
        <div class="flex justify-center">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
                <div class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center font-bold text-sm shadow-md group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                    </svg>
                </div>
                <span class="text-xl font-bold text-slate-900 tracking-tight">
                    Kampus<span class="text-slate-500 font-normal">LMS</span>
                </span>
            </a>
        </div>

        <h1 class="mt-6 text-center text-2xl font-bold tracking-tight text-slate-900">
            Masuk ke Akun Anda
        </h1>
        <p class="mt-1 text-center text-xs text-slate-600">
            Sistem Pembelajaran Terpadu — Semester Ganjil 2026/2027
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md px-4">
        <div class="bg-white py-8 px-5 sm:px-10 shadow-sm border border-slate-200 rounded-2xl">
            
            {{-- Flash Notification Alerts --}}
            @if (session('success'))
                <div class="mb-5 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-800 flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-rose-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div>
                        <p class="font-semibold">Autentikasi Gagal</p>
                        <p class="mt-0.5">{{ $errors->first() }}</p>
                    </div>
                </div>
            @endif

            {{-- Form Login Utama --}}
            <form action="{{ route('login') }}" method="POST" class="space-y-5" novalidate>
                @csrf

                {{-- Input Email --}}
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-700">
                        Alamat Email
                    </label>
                    <div class="mt-1.5 relative">
                        <input 
                            id="email" 
                            name="email" 
                            type="email" 
                            autocomplete="email" 
                            required 
                            value="{{ old('email') }}"
                            placeholder="nama@kampuslms.test"
                            class="block w-full rounded-xl border @error('email') border-rose-300 ring-rose-200 focus:border-rose-500 @else border-slate-300 focus:border-slate-900 focus:ring-slate-900 @enderror bg-white px-3.5 py-2 text-xs text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-offset-0 transition-colors"
                        >
                    </div>
                </div>

                {{-- Input Password --}}
                <div>
                    <div class="flex items-center justify-between">
                        <label for="password" class="block text-xs font-semibold text-slate-700">
                            Kata Sandi
                        </label>
                        <span class="text-[11px] text-slate-500 font-mono">Min. 8 karakter</span>
                    </div>
                    <div class="mt-1.5 relative">
                        <input 
                            id="password" 
                            name="password" 
                            type="password" 
                            autocomplete="current-password" 
                            required 
                            placeholder="••••••••"
                            class="block w-full rounded-xl border @error('password') border-rose-300 ring-rose-200 focus:border-rose-500 @else border-slate-300 focus:border-slate-900 focus:ring-slate-900 @enderror bg-white px-3.5 py-2 text-xs text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-offset-0 transition-colors"
                        >
                    </div>
                </div>

                {{-- Remember Me & Info --}}
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input 
                            type="checkbox" 
                            name="remember" 
                            id="remember"
                            class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900 cursor-pointer"
                        >
                        <span class="text-xs text-slate-600 select-none">Ingat saya di perangkat ini</span>
                    </label>
                </div>

                {{-- Tombol Submit --}}
                <div>
                    <button 
                        type="submit" 
                        class="w-full flex justify-center items-center py-2.5 px-4 border border-transparent rounded-xl shadow-xs text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 transition-all cursor-pointer"
                    >
                        Masuk ke Sistem
                    </button>
                </div>
            </form>

            {{-- 1-Click Demo Credentials Card --}}
            <div class="mt-8 pt-6 border-t border-slate-100">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        ⚡ Akun Demo Pengujian (1-Klik)
                    </span>
                    <span class="text-[10px] text-slate-400 font-mono">Password: password</span>
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <button 
                        type="button" 
                        onclick="fillDemo('admin@kampuslms.test', 'password')"
                        class="px-2 py-2 rounded-lg border border-slate-200 bg-slate-50 hover:bg-rose-50 hover:border-rose-200 hover:text-rose-700 text-slate-700 text-[11px] font-medium text-center transition-colors cursor-pointer"
                    >
                        <div class="font-bold">🛡️ Admin</div>
                        <div class="text-[9px] text-slate-500 truncate">admin@...</div>
                    </button>

                    <button 
                        type="button" 
                        onclick="fillDemo('dosen@kampuslms.test', 'password')"
                        class="px-2 py-2 rounded-lg border border-slate-200 bg-slate-50 hover:bg-sky-50 hover:border-sky-200 hover:text-sky-700 text-slate-700 text-[11px] font-medium text-center transition-colors cursor-pointer"
                    >
                        <div class="font-bold">👨‍🏫 Dosen</div>
                        <div class="text-[9px] text-slate-500 truncate">dosen@...</div>
                    </button>

                    <button 
                        type="button" 
                        onclick="fillDemo('mahasiswa@kampuslms.test', 'password')"
                        class="px-2 py-2 rounded-lg border border-slate-200 bg-slate-50 hover:bg-emerald-50 hover:border-emerald-200 hover:text-emerald-700 text-slate-700 text-[11px] font-medium text-center transition-colors cursor-pointer"
                    >
                        <div class="font-bold">🎓 Mahasiswa</div>
                        <div class="text-[9px] text-slate-500 truncate">mhs@...</div>
                    </button>
                </div>
            </div>

        </div>

        <p class="mt-6 text-center text-[11px] text-slate-500">
            Terproteksi dengan CSRF Token & Pembatasan Laju (Rate Limiting).
        </p>
    </div>

    <script>
        function fillDemo(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
            document.getElementById('email').focus();
        }
    </script>
</body>
</html>
