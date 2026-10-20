<x-layout title="Buat Materi — {{ $course->name }}">

    {{-- Kembali & judul aksi --}}
    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('courses.show', $course) }}" class="btn-outline text-xs inline-flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-600" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Kembali ke mata kuliah
        </a>
        <span class="text-xs font-semibold text-slate-600">Tambah Materi Baru</span>
    </div>

    <div class="lms-card p-6 sm:p-7 max-w-2xl">
        <h1 class="text-xl font-bold text-slate-900 tracking-tight mb-6">
            Buat Materi untuk {{ $course->name }}
        </h1>

        @can('create', [\App\Models\Material::class, $course])
        <form action="{{ route('courses.materials.store', $course) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="space-y-5" id="material-form-container">

                {{-- Judul --}}
                <div>
                    <label for="title" class="block text-sm font-semibold text-slate-800 mb-1.5">
                        Judul Materi <span class="text-rose-600">*</span>
                    </label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="{{ old('title') }}"
                        class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 ring-focus transition-shadow focus:border-sky-600 focus:ring-sky-600"
                        placeholder="Contoh: Pertemuan 1: Pengantar Arsitektur Web"
                        autocomplete="off"
                        required
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
                    @php
                        $selectedType = old('type', 'file');
                    @endphp
                    <div class="grid grid-cols-2 gap-3">
                        <label class="relative cursor-pointer rounded-lg border-2 p-3 text-center transition-all border-slate-200 bg-white hover:border-slate-300 has-[:checked]:border-sky-600 has-[:checked]:bg-sky-50/70" id="type_label_file">
                            <input
                                type="radio"
                                name="type"
                                value="file"
                                {{ $selectedType === 'file' ? 'checked' : '' }}
                                class="sr-only"
                            />
                            <div class="flex items-center justify-center gap-2">
                                <svg class="w-4 h-4 text-sky-700" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                                <span class="text-sm font-semibold text-slate-800">Unggah Berkas</span>
                            </div>
                        </label>

                        <label class="relative cursor-pointer rounded-lg border-2 p-3 text-center transition-all border-slate-200 bg-white hover:border-slate-300 has-[:checked]:border-sky-600 has-[:checked]:bg-sky-50/70" id="type_label_link">
                            <input
                                type="radio"
                                name="type"
                                value="link"
                                {{ $selectedType === 'link' ? 'checked' : '' }}
                                class="sr-only"
                            />
                            <div class="flex items-center justify-center gap-2">
                                <svg class="w-4 h-4 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
                                </svg>
                                <span class="text-sm font-semibold text-slate-800">Tautan Eksternal</span>
                            </div>
                        </label>
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

                {{-- Input Berkas (Tipe File) --}}
                <div id="group_file" class="group-file-panel space-y-1.5">
                    <label for="file" class="block text-sm font-semibold text-slate-800 mb-1">
                        Pilih Berkas Materi <span class="text-rose-600">*</span>
                    </label>
                    <div class="rounded-lg border border-slate-200 bg-slate-50/60 p-3 hover:border-slate-300 transition-colors">
                        <input
                            id="file"
                            name="file"
                            type="file"
                            accept=".pdf,.pptx,.docx"
                            class="block w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 cursor-pointer"
                        />
                        <p class="text-[11px] text-slate-500 mt-2">
                            Format yang didukung: <span class="font-medium text-slate-700">PDF, PPTX, DOCX</span> (Maks. 20 MB).
                        </p>
                    </div>
                    @error('file')
                        <p class="mt-1.5 text-xs text-rose-700 font-medium flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Input Tautan (Tipe Link) --}}
                <div id="group_link" class="group-link-panel space-y-1.5">
                    <label for="external_url" class="block text-sm font-semibold text-slate-800 mb-1.5">
                        Tautan Referensi / URL Luar <span class="text-rose-600">*</span>
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
                            value="{{ old('external_url') }}"
                            class="w-full rounded-lg border border-slate-200 bg-white pl-10 pr-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 ring-focus transition-shadow focus:border-sky-600 focus:ring-sky-600"
                            placeholder="https://example.com/modul-pembelajaran.pdf"
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
                        Isi / Deskripsi Materi <span class="text-rose-600">*</span>
                    </label>
                    <textarea
                        id="description"
                        name="description"
                        rows="5"
                        required
                        class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 ring-focus transition-shadow focus:border-sky-600 focus:ring-sky-600 resize-y"
                        placeholder="Ringkasan materi, poin pembahasan, atau petunjuk tambahan untuk peserta."
                    >{{ old('description') }}</textarea>
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
                    Simpan Materi
                </button>
            </div>
        </form>
        @endcan
    </div>

    {{-- Modern Web Guidance: child-state-based-styling using CSS :has() --}}
    <style>
        #material-form-container:has(input[name="type"][value="file"]:checked) .group-file-panel { display: block; }
        #material-form-container:has(input[name="type"][value="file"]:checked) .group-link-panel { display: none; }
        #material-form-container:has(input[name="type"][value="link"]:checked) .group-file-panel { display: none; }
        #material-form-container:has(input[name="type"][value="link"]:checked) .group-link-panel { display: block; }
    </style>

    <script>
        // Fallback untuk browser lawas yang belum mendukung CSS :has()
        if (!CSS.supports('selector(:has(*))')) {
            const formContainer = document.getElementById('material-form-container');
            const filePanel = formContainer?.querySelector('.group-file-panel');
            const linkPanel = formContainer?.querySelector('.group-link-panel');
            const radios = formContainer?.querySelectorAll('input[name="type"]');

            const syncFallback = () => {
                const checked = formContainer?.querySelector('input[name="type"]:checked')?.value;
                if (filePanel && linkPanel) {
                    filePanel.style.display = checked === 'file' ? 'block' : 'none';
                    linkPanel.style.display = checked === 'link' ? 'block' : 'none';
                }
            };
            radios?.forEach(r => r.addEventListener('change', syncFallback));
            syncFallback();
        }
    </script>

</x-layout>
