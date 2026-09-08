<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    {{-- Menyesuaikan tampilan halaman dengan perangkat pengguna. --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Menggunakan judul yang dikirim oleh setiap halaman. --}}
    <title>{{ $title ?? 'KampusLMS' }}</title>

    {{-- Memuat CSS dan JavaScript melalui Vite. --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>

    {{-- Navbar untuk berpindah ke halaman utama aplikasi. --}}
    <nav>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <a href="{{ route('courses.index') }}">Mata Kuliah</a>
        <a href="{{ route('tentang') }}">Tentang</a>
    </nav>

    {{-- Slot menjadi tempat isi dari halaman yang menggunakan x-layout. --}}
    <main>
        {{ $slot }}
    </main>

</body>
</html>