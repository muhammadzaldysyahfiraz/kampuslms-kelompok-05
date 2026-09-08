{{-- Menggunakan komponen x-layout agar halaman memiliki layout yang sama. --}}
<x-layout title="Daftar Mata Kuliah">

    {{-- Judul halaman daftar mata kuliah. --}}
    <h1>Daftar Mata Kuliah</h1>

    {{-- Tabel digunakan untuk menampilkan data mata kuliah secara terstruktur. --}}
    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama Mata Kuliah</th>
                <th>SKS</th>
                <th>Dosen</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            {{-- Melakukan perulangan untuk menampilkan setiap mata kuliah. --}}
            @foreach ($courses as $course)
                <tr>
                    <td>{{ $course['code'] }}</td>
                    <td>{{ $course['name'] }}</td>
                    <td>{{ $course['sks'] }}</td>
                    <td>{{ $course['lecturer'] }}</td>

                    {{-- route() digunakan agar URL tidak ditulis secara hardcode. --}}
                    <td>
                        <a href="{{ route('courses.show', $course['id']) }}">
                            Lihat Detail
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</x-layout>