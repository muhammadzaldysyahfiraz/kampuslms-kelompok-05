<x-layout title="Daftar Mata Kuliah">

    <h1>Daftar Mata Kuliah</h1>

    @if (session('success'))
        <p>{{ session('success') }}</p>
    @endif

    <a href="{{ route('courses.create') }}">
        Tambah Mata Kuliah
    </a>

    <br><br>

    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama</th>
                <th>SKS</th>
                <th>Dosen</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($courses as $course)
                <tr>
                    <td>{{ $course->code }}</td>
                    <td>{{ $course->name }}</td>
                    <td>{{ $course->sks }}</td>
                    <td>{{ $course->lecturer->name }}</td>
                    <td>{{ $course->status }}</td>

                    <td>
                        <a href="{{ route('courses.show', $course) }}">
                            Lihat
                        </a>

                        <a href="{{ route('courses.edit', $course) }}">
                            Edit
                        </a>

                        <form
                            action="{{ route('courses.destroy', $course) }}"
                            method="POST"
                            style="display:inline"
                        >
                            @csrf
                            @method('DELETE')

                            <button type="submit">
                                Hapus
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</x-layout>