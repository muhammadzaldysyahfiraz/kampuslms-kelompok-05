<x-layout title="Detail Mata Kuliah">

    <h1>{{ $course->name }}</h1>

    <p>Kode: {{ $course->code }}</p>

    <p>SKS: {{ $course->sks }}</p>

    <p>Dosen: {{ $course->lecturer->name }}</p>

    <p>Deskripsi: {{ $course->description }}</p>

    <p>Status: {{ $course->status }}</p>

    <a href="{{ route('courses.edit', $course) }}">
        Edit
    </a>

    <br>

    <a href="{{ route('courses.index') }}">
        Kembali ke Daftar
    </a>

</x-layout>