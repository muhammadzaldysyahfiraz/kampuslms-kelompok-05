<x-layout title="Edit Mata Kuliah">

    <h1>Edit Mata Kuliah</h1>

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form
        action="{{ route('courses.update', $course) }}"
        method="POST"
    >
        @csrf
        @method('PUT')

        <div>
            <label>Kode</label>
            <input
                type="text"
                name="code"
                value="{{ old('code', $course->code) }}"
            >
        </div>

        <div>
            <label>Nama Mata Kuliah</label>
            <input
                type="text"
                name="name"
                value="{{ old('name', $course->name) }}"
            >
        </div>

        <div>
            <label>Deskripsi</label>
            <textarea name="description">{{ old('description', $course->description) }}</textarea>
        </div>

        <div>
            <label>SKS</label>
            <input
                type="number"
                name="sks"
                value="{{ old('sks', $course->sks) }}"
            >
        </div>

        <div>
            <label>Dosen</label>
            <select name="lecturer_id">
                @foreach ($lecturers as $lecturer)
                    <option
                        value="{{ $lecturer->id }}"
                        {{ old('lecturer_id', $course->lecturer_id) == $lecturer->id ? 'selected' : '' }}
                    >
                        {{ $lecturer->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <br>

        <button type="submit">
            Update
        </button>

        <a href="{{ route('courses.show', $course) }}">
            Batal
        </a>
    </form>

</x-layout>