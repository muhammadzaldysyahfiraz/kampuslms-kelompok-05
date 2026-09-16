<x-layout title="Edit: {{ $course->name }}">

    {{-- Breadcrumb Navigasi --}}
    <div class="mb-6">
        <a href="{{ route('courses.show', $course) }}" class="btn-outline text-xs py-2 w-fit inline-flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-600" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            <span>Kembali ke Detail Mata Kuliah</span>
        </a>
    </div>

    {{-- Form Container --}}
    <div class="max-w-2xl mx-auto">
        <div class="lms-card p-6 sm:p-8">
            
            {{-- Form Header --}}
            <div class="border-b border-slate-100 pb-5 mb-6">
                <div class="flex items-center gap-3 mb-1.5">
                    <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-800 flex items-center justify-center font-bold" aria-hidden="true">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                        </svg>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                        Edit Mata Kuliah
                    </h1>
                </div>
                <p class="text-xs text-slate-600 font-normal">
                    Perbarui informasi kurikulum, bobot SKS, atau penugasan dosen pengampu.
                </p>
            </div>

            {{-- Error Summary Alert if any --}}
            @if ($errors->any())
                <div class="p-4 rounded-lg bg-rose-50 border border-rose-200 mb-6" role="alert" aria-labelledby="form-error-summary">
                    <div class="flex items-center gap-2 text-rose-800 text-xs font-bold mb-1">
                        <svg class="w-4 h-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                        <span id="form-error-summary">Terdapat beberapa kesalahan pengisian formulir:</span>
                    </div>
                    <ul class="list-disc list-inside text-xs text-rose-800 space-y-0.5 font-normal">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Form Edit Data --}}
            <form action="{{ route('courses.update', $course) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                {{-- Baris 1: Kode Mata Kuliah & SKS --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label for="code" class="form-label">
                            Kode Mata Kuliah <span class="text-rose-700" aria-hidden="true">*</span>
                        </label>
                        <input
                            type="text"
                            id="code"
                            name="code"
                            value="{{ old('code', $course->code) }}"
                            maxlength="10"
                            class="form-input font-mono uppercase {{ $errors->has('code') ? 'border-rose-400 focus:border-rose-500' : '' }}"
                            aria-required="true"
                            aria-invalid="{{ $errors->has('code') ? 'true' : 'false' }}"
                            aria-describedby="code-hint @error('code') code-error @enderror"
                            required
                        >
                        <p id="code-hint" class="text-[11px] text-slate-600 mt-1 font-normal">Maksimal 10 karakter alfanumerik (contoh: SI2514024).</p>
                        @error('code')
                            <p id="code-error" class="text-xs text-rose-700 mt-1 font-semibold" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="sks" class="form-label">
                            SKS <span class="text-rose-700" aria-hidden="true">*</span>
                        </label>
                        <input
                            type="number"
                            id="sks"
                            name="sks"
                            min="1"
                            max="6"
                            step="1"
                            value="{{ old('sks', $course->sks) }}"
                            class="form-input text-center font-bold font-mono {{ $errors->has('sks') ? 'border-rose-400 focus:border-rose-500' : '' }}"
                            aria-required="true"
                            aria-invalid="{{ $errors->has('sks') ? 'true' : 'false' }}"
                            aria-describedby="sks-hint @error('sks') sks-error @enderror"
                            required
                        >
                        <p id="sks-hint" class="text-[11px] text-slate-600 mt-1 font-normal text-center">Beban 1–6 SKS</p>
                        @error('sks')
                            <p id="sks-error" class="text-xs text-rose-700 mt-1 font-semibold" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Baris 2: Nama Mata Kuliah --}}
                <div>
                    <label for="name" class="form-label">
                        Nama Mata Kuliah <span class="text-rose-700" aria-hidden="true">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $course->name) }}"
                        class="form-input {{ $errors->has('name') ? 'border-rose-400 focus:border-rose-500' : '' }}"
                        aria-required="true"
                        aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                        aria-describedby="name-hint @error('name') name-error @enderror"
                        required
                    >
                    <p id="name-hint" class="text-[11px] text-slate-600 mt-1 font-normal">Nama resmi kurikulum mata kuliah.</p>
                    @error('name')
                        <p id="name-error" class="text-xs text-rose-700 mt-1 font-semibold" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Baris 3: Dosen Pengampu & Status --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="lecturer_id" class="form-label">
                            Dosen Pengampu <span class="text-rose-700" aria-hidden="true">*</span>
                        </label>
                        <select
                            id="lecturer_id"
                            name="lecturer_id"
                            class="form-input {{ $errors->has('lecturer_id') ? 'border-rose-400 focus:border-rose-500' : '' }}"
                            aria-required="true"
                            aria-invalid="{{ $errors->has('lecturer_id') ? 'true' : 'false' }}"
                            aria-describedby="lecturer_id-hint @error('lecturer_id') lecturer_id-error @enderror"
                            required
                        >
                            @foreach ($lecturers as $lecturer)
                                <option
                                    value="{{ $lecturer->id }}"
                                    {{ old('lecturer_id', $course->lecturer_id) == $lecturer->id ? 'selected' : '' }}
                                >
                                    {{ $lecturer->name }} ({{ $lecturer->nim_nip ?? 'Dosen' }})
                                </option>
                            @endforeach
                        </select>
                        <p id="lecturer_id-hint" class="text-[11px] text-slate-600 mt-1 font-normal">Dosen yang mengampu mata kuliah ini.</p>
                        @error('lecturer_id')
                            <p id="lecturer_id-error" class="text-xs text-rose-700 mt-1 font-semibold" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status" class="form-label">
                            Status Perkuliahan <span class="text-rose-700" aria-hidden="true">*</span>
                        </label>
                        <select
                            id="status"
                            name="status"
                            class="form-input"
                            aria-describedby="status-hint"
                            required
                        >
                            <option value="draft" {{ old('status', $course->status) === 'draft' ? 'selected' : '' }}>Draft (Belum Dibuka)</option>
                            <option value="active" {{ old('status', $course->status) === 'active' ? 'selected' : '' }}>Aktif (Sedang Berjalan)</option>
                            <option value="archived" {{ old('status', $course->status) === 'archived' ? 'selected' : '' }}>Diarsipkan</option>
                        </select>
                        <p id="status-hint" class="text-[11px] text-slate-600 mt-1 font-normal">Visibilitas mata kuliah bagi mahasiswa.</p>
                    </div>
                </div>

                {{-- Baris 4: Deskripsi / Silabus --}}
                <div>
                    <label for="description" class="form-label">
                        Deskripsi / Silabus Singkat
                    </label>
                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                        class="form-input {{ $errors->has('description') ? 'border-rose-400 focus:border-rose-500' : '' }}"
                        aria-invalid="{{ $errors->has('description') ? 'true' : 'false' }}"
                        aria-describedby="description-hint @error('description') description-error @enderror"
                    >{{ old('description', $course->description) }}</textarea>
                    <p id="description-hint" class="text-[11px] text-slate-600 mt-1 font-normal">Penjelasan topik, silabus, atau deskripsi ringkas.</p>
                    @error('description')
                        <p id="description-error" class="text-xs text-rose-700 mt-1 font-semibold" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tombol Aksi Form --}}
                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="{{ route('courses.show', $course) }}" class="btn-outline">
                        Batal
                    </a>
                    <button type="submit" class="btn-primary">
                        <svg class="w-4 h-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        <span>Perbarui Mata Kuliah</span>
                    </button>
                </div>

            </form>

        </div>
    </div>

</x-layout>