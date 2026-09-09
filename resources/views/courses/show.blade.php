<x-layout title="Detail Mata Kuliah">

    <h1>{{ $course['name'] }}</h1>

    <p>Kode: {{ $course['code'] }}</p>

    <p>SKS: {{ $course['sks'] }}</p>

    <p>Dosen: {{ $course['lecturer'] }}</p>

    <a href="{{ route('courses.index') }}">
        Kembali ke Daftar Mata Kuliah
    </a>

</x-layout>