{{-- Menggunakan x-layout agar halaman daftar menggunakan
     struktur HTML yang sudah dibuat di layout.blade.php. --}}
<x-layout title="Daftar Mata Kuliah">

    {{-- Judul halaman daftar mata kuliah. --}}
    <h1>Daftar Mata Kuliah</h1>

    {{-- Melakukan perulangan terhadap seluruh data mata kuliah
         yang dikirim oleh CourseController@index. --}}
    @foreach ($courses as $id => $course)

        <article>

            {{-- Menampilkan nama mata kuliah.
                 {{ }} digunakan agar output di-escape oleh Blade. --}}
            <h2>{{ $course['name'] }}</h2>

            {{-- Menampilkan kode mata kuliah. --}}
            <p>
                Kode: {{ $course['code'] }}
            </p>

            {{-- Menampilkan jumlah SKS. --}}
            <p>
                SKS: {{ $course['sks'] }}
            </p>

            {{-- Menampilkan nama dosen pengampu. --}}
            <p>
                Dosen: {{ $course['lecturer'] }}
            </p>

            {{-- Membuat tautan menuju halaman detail.
                 route() digunakan agar URI tidak ditulis secara hardcode. --}}
            <a href="{{ route('courses.show', ['course' => $id]) }}">
                Lihat Detail
            </a>

        </article>

        <hr>

    @endforeach

</x-layout>