<x-layout title="Tambah Mata Kuliah">

    <h1>Tambah Mata Kuliah</h1>

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ route('courses.store') }}" method="POST">
        @csrf

        <div>
            <label>Kode</label>
            <input
                type="text"
                name="code"
                value="{{ old('code') }}"
            >
        </div>

        <div>
            <label>Nama Mata Kuliah</label>
            <input
                type="text"
                name="name"
                value="{{ old('name') }}"
            >
        </div>

        <div>
            <label>Deskripsi</label>
            <textarea name="description">{{ old('description') }}</textarea>
        </div>

        <div>
            <label>SKS</label>
            <input
                type="number"
                name="sks"
                value="{{ old('sks') }}"
            >
        </div>

        <div>
            <label>Dosen</label>
            <select name="lecturer_id">
                @foreach ($lecturers as $lecturer)
                    <option
                        value="{{ $lecturer->id }}"
                        {{ old('lecturer_id') == $lecturer->id ? 'selected' : '' }}
                    >
                        {{ $lecturer->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <br>

        <button type="submit">
            Simpan
        </button>

        <a href="{{ route('courses.index') }}">
            Batal
        </a>
    </form>

</x-layout>