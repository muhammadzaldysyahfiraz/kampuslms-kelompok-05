<x-layout title="Edit Materi — {{ $material->title }}">

    {{-- Kembali & judul aksi --}}
    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('courses.show', $material->course) }}" class="btn-outline text-xs inline-flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-600" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Kembali ke mata kuliah
        </a>
        <span class="text-xs font-semibold text-slate-600">Edit Materi</span>
    </div>

    <div class="lms-card p-6 sm:p-7 max-w-2xl">
        <h1 class="text-xl font-bold text-slate-900 tracking-tight mb-6">
            Edit Materi
            <span class="font-normal text-slate-600">— {{ $material->title }}</span>
        </h1>

        <form action="{{ route('materials.update', $material) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-5">

                {{-- Judul --}}
                <div>
                    <label for="title" class="block text-sm font-semibold text-slate-800 mb-1.5">
                        Judul Materi <span class="text-rose-600">*</span>
                    </label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="{{ old('title', $material->title) }}"
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

                {{-- Tipe --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-800 mb-1.5">
                        Tipe Materi <span class="text-rose-600">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        @foreach (['link', 'file'] as $option)
                            @php
                                $checked = old('type', $material->type) === $option;
                            @endphp
                            <label class="relative cursor-pointer rounded-lg border-2 {{ $checked ? 'border-sky-600 bg-sky-50/70' : 'border-slate-200 bg-white' }} px-3.5 py-2.5 text-center transition-all hover:border-slate-300">
                                <input
                                    type="radio"
                                    name="type"
                                    value="{{ $option }}"
                                    {{ $checked ? 'checked' : '' }}
                                    class="sr-only"
                                />
                                <span class="text-sm font-semibold text-slate-800">
                                    {{ ucfirst($option) }}
                                </span>
                                @if ($checked)
                                    <span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-sky-600" aria-hidden="true"></span>
                                @endif
                            </label>
                        @endforeach
                    </div>
                    @error('type')
                        <p class="mt-1.5 text-xs text-rose-700 font-medium flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- External url --}}
                <div>
                    <label for="external_url" class="block text-sm font-semibold text-slate-800 mb-1.5">
                        Tautan Referensi / URL Luar
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                            <svg class="w-4 h-4 text-slate-400" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                            </svg>
                        </div>
                        <input
                            type="url"
                            id="external_url"
                            name="external_url"
                            value="{{ old('external_url', $material->external_url) }}"
                            class="w-full rounded-lg border border-slate-200 bg-white pl-10 pr-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 ring-focus transition-shadow focus:border-sky-600 focus:ring-sky-600"
                            placeholder="https://example.com/modul.pdf"
                        />
                    </div>
                    @error('external_url')
                        <p class="mt-1.5 text-xs text-rose-700 font-medium flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Deskripsi --}}
                <div>
                    <label for="description" class="block text-sm font-semibold text-slate-800 mb-1.5">
                        Isi / Deskripsi Materi
                    </label>
                    <textarea
                        id="description"
                        name="description"
                        rows="6"
                        class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 ring-focus transition-shadow focus:border-sky-600 focus:ring-sky-600 resize-y"
                    >{{ old('description', $material->description) }}</textarea>
                    @error('description')
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
                <a href="{{ route('courses.show', $material->course) }}" class="btn-outline text-xs w-full sm:w-auto inline-flex items-center justify-center gap-2">
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
