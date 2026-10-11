<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Reset Kata Sandi — KampusLMS</title>@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body class="min-h-screen bg-slate-50 flex items-center justify-center p-6"><main class="w-full max-w-md rounded-xl bg-white p-8 shadow">
<h1 class="text-xl font-bold">Lupa kata sandi</h1><p class="mt-2 text-sm text-slate-600">Masukkan email akun Anda. Jika terdaftar, kami mengirim tautan reset.</p>
@if (session('status'))<p role="status" class="mt-4 text-sm text-emerald-700">{{ session('status') }}</p>@endif
<form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">@csrf
<label class="block text-sm">Email<input name="email" type="email" required value="{{ old('email') }}" class="mt-1 w-full rounded border p-2"></label>@error('email')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
<button class="rounded bg-slate-900 px-4 py-2 text-white">Kirim tautan reset</button></form><a class="mt-4 block text-sm underline" href="{{ route('login') }}">Kembali ke login</a>
</main></body></html>
