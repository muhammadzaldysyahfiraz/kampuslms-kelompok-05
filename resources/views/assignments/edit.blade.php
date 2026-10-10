<x-layout title="Edit Tugas — {{ $assignment->title }}">

    {{-- Kembali & judul aksi --}}
    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('courses.show', $assignment->course) }}" class="btn-outline text-xs inline-flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-600" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Kembali ke mata kuliah
        </a>
        <span class="text-xs font-semibold text-slate-600">Edit Tugas</span>
    </div>

    <div class="lms-card p-6 sm:p-7 max-w-2xl">
        <h1 class="text-xl font-bold text-slate-900 tracking-tight mb-6">
            Edit Tugas
            <span class="font-normal text-slate-600">— {{ $assignment->title }}</span>
        </h1>

        <form action="{{ route('dosen.courses.assignments.update', [$assignment->course, $assignment]) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-5">

                {{-- Judul --}}
                <div>
                    <label for="title" class="block text-sm font-semibold text-slate-800 mb-1.5">
                        Judul Tugas <span class="text-rose-600">*</span>
                    </label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="{{ old('title', $assignment->title) }}"
                        class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 ring-focus transition-shadow focus:border-sky-600 focus:ring-sky-600"
                        autocomplete="off"
                    />
                    @error('title')
                        <p class="mt-1.5 text-xs text-rose-700 font-medium flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Deadline --}}
                <div>
                    <label for="due_at" class="block text-sm font-semibold text-slate-800 mb-1.5">
                        Batas Pengumpulan <span class="text-rose-600">*</span>
                    </label>
                    <input
                        type="datetime-local"
                        id="due_at"
                        name="due_at"
                        value="{{ old('due_at', $assignment->due_at?->format('Y-m-d\TH:i')) }}"
                        class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 ring-focus transition-shadow focus:border-sky-600 focus:ring-sky-600"
                    />
                    @error('due_at')
                        <p class="mt-1.5 text-xs text-rose-700 font-medium flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Nilai maksimal --}}
                <div>
                    <label for="max_score" class="block text-sm font-semibold text-slate-800 mb-1.5">
                        Nilai Maksimal <span class="text-rose-600">*</span>
                    </label>
                    <input
                        type="number"
                        id="max_score"
                        name="max_score"
                        min="1"
                        max="100"
                        value="{{ old('max_score', $assignment->max_score) }}"
                        class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 ring-focus transition-shadow focus:border-sky-600 focus:ring-sky-600"
                    />
                    @error('max_score')
                        <p class="mt-1.5 text-xs text-rose-700 font-medium flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Izinkan terlambat --}}
                <label class="inline-flex items-center gap-2.5 cursor-pointer select-none rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 hover:border-slate-300 transition-colors">
                    <input
                        type="checkbox"
                        name="allow_late"
                        value="1"
                        {{ old('allow_late', $assignment->allow_late) ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-slate-300 text-sky-600 focus:ring-sky-600 accent-sky-600 cursor-pointer"
                    />
                    <span class="font-medium">Izinkan peserta mengumpulkan tugas setelah batas waktu</span>
                </label>

                {{-- Instruksi --}}
                <div>
                    <label for="instructions" class="block text-sm font-semibold text-slate-800 mb-1.5">
                        Instruksi Tugas
                    </label>
                    <textarea
                        id="instructions"
                        name="instructions"
                        rows="6"
                        class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 ring-focus transition-shadow focus:border-sky-600 focus:ring-sky-600 resize-y"
                    >{{ old('instructions', $assignment->instructions) }}</textarea>
                    @error('instructions')
                        <p class="mt-1.5 text-xs text-rose-700 font-medium flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>

            <div class="mt-8 flex flex-col sm:flex-row sm:items-center sm:justify-end sm:gap-3">
                <a href="{{ route('courses.show', $assignment->course) }}" class="btn-outline text-xs w-full sm:w-auto inline-flex items-center justify-center gap-2">
                    <svg class="w-4 h-4 text-slate-600" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 10.5 15 15.75 10.5M19.5 15h-6v-6" />
                    </svg>
                    Batal
                </a>
                <button
                    type="submit"
                    class="btn-primary text-xs w-full sm:w-auto inline-flex items-center justify-center gap-2"
                >
                    <svg class="w-4 h-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487 1.682 19.07a1.75 1.75 0 0 0 1.588 3.07h14.768a1.75 1.75 0 0 0 1.588-3.07l-5.015-13.52A1.75 1.75 0 0 0 6.92 4.487H5a1.75 1.75 0 0 0-1-.43M15 11.25h3m-3 0-3 3m3-3 3 3" />
                    </svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

</x-layout>
