<x-layout title="Buat Tugas — {{ $course->name }}">

    {{-- Kembali & judul aksi --}}
    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('courses.show', $course) }}" class="btn-outline text-xs inline-flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-600" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Kembali ke mata kuliah
        </a>
        <span class="text-xs font-semibold text-slate-600">Tambah Tugas Baru</span>
    </div>

    <div class="lms-card p-6 sm:p-7 max-w-2xl">
        <h1 class="text-xl font-bold text-slate-900 tracking-tight mb-6">
            Buat Tugas untuk {{ $course->name }}
        </h1>

        <form action="{{ route('dosen.courses.assignments.store', [$course, 'assignment' => null]) }}" method="POST">
            @csrf

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
                        value="{{ old('title') }}"
                        class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 ring-focus transition-shadow focus:border-sky-600 focus:ring-sky-600"
                        placeholder="Contoh: Tugas 1 — Analisis Kebutuhan"
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
                        value="{{ old('due_at') ? old('due_at')->format('Y-m-d\TH:i') : '' }}"
                        class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 ring-focus transition-shadow focus:border-sky-600 focus:ring-sky-600"
                        placeholder="Pilih tanggal dan waktu"
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
                        value="{{ old('max_score') }}"
                        class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 ring-focus transition-shadow focus:border-sky-600 focus:ring-sky-600"
                        placeholder="100"
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
                        {{ old('allow_late') ? 'checked' : '' }}
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
                        placeholder="Jelaskan tujuan, langkah pengerjaan, format pengiriman, dan kriteria penilaian."
                    >{{ old('instructions') }}</textarea>
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
                <a href="{{ route('courses.show', $course) }}" class="btn-outline text-xs w-full sm:w-auto inline-flex items-center justify-center gap-2">
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
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Simpan Tugas
                </button>
            </div>
        </form>
    </div>

</x-layout>
