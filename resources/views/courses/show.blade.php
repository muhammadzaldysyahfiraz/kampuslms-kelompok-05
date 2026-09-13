{{-- Menggunakan x-layout agar halaman detail memiliki
     struktur yang sama dengan halaman lainnya. --}}
<x-layout title="Detail Mata Kuliah">

@php
    $nama = "<script>alert('XSS WEBSITE DI HACK')</script>";
@endphp

{{ $nama }}

    {{-- Judul halaman detail. --}}
    <h1>Detail Mata Kuliah</h1>

    {{-- Menampilkan nama mata kuliah yang dikirim
         oleh CourseController@show. --}}
    <h2>{{ $course['name'] }}</h2>

    {{-- Menampilkan kode mata kuliah. --}}
    <p>
        <strong>Kode:</strong>
        {{ $course['code'] }}
    </p>

    {{-- Menampilkan jumlah SKS. --}}
    <p>
        <strong>SKS:</strong>
        {{ $course['sks'] }}
    </p>

    {{-- Menampilkan dosen pengampu. --}}
    <p>
        <strong>Dosen:</strong>
        {{ $course['lecturer'] }}
    </p>

    {{-- Kembali ke halaman daftar mata kuliah.
         route() digunakan agar tidak menulis URL secara hardcode. --}}
    <a href="{{ route('courses.index') }}">
        Kembali ke Daftar Mata Kuliah
    </a>

</x-layout>