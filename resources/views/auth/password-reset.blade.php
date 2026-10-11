<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Buat Kata Sandi Baru — KampusLMS</title>@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body class="min-h-screen bg-slate-50 flex items-center justify-center p-6"><main class="w-full max-w-md rounded-xl bg-white p-8 shadow">
<h1 class="text-xl font-bold">Buat kata sandi baru</h1><form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4">@csrf
<input type="hidden" name="token" value="{{ $token }}">
<label class="block text-sm">Email<input name="email" type="email" required value="{{ old('email', $email) }}" class="mt-1 w-full rounded border p-2"></label>@error('email')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
<label class="block text-sm">Kata sandi baru<input name="password" type="password" required autocomplete="new-password" class="mt-1 w-full rounded border p-2"></label>@error('password')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
<label class="block text-sm">Ulangi kata sandi<input name="password_confirmation" type="password" required autocomplete="new-password" class="mt-1 w-full rounded border p-2"></label>
<button class="rounded bg-slate-900 px-4 py-2 text-white">Simpan kata sandi</button></form></main></body></html>
