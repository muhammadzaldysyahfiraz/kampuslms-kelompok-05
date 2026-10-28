<x-layout title="{{ $assignment->title }} — {{ $assignment->course->name }}">

    {{-- Breadcrumb & aksi --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-2 text-xs text-slate-600">
            <a href="{{ route('courses.index') }}" class="text-slate-500 hover:text-slate-800 transition-colors">Mata kuliah</a>
            <span class="text-slate-300" aria-hidden="true">/</span>
            <a href="{{ route('courses.show', $assignment->course) }}" class="text-slate-500 hover:text-slate-800 transition-colors">
                {{ $assignment->course->name }}
            </a>
            <span class="text-slate-300" aria-hidden="true">/</span>
            <span class="text-slate-800 font-semibold">{{ $assignment->title }}</span>
        </div>

        @can('update', $assignment)
            <div class="flex items-center gap-2">
                @if (($assignment->status ?? 'draft') === 'draft')
                    <form action="{{ route('assignments.update', $assignment) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="published" />
                        <button type="submit" class="btn-primary text-xs inline-flex items-center gap-1.5 py-2 px-3 cursor-pointer" title="Publikasikan tugas agar mahasiswa menerima notifikasi dan dapat mengumpulkan tugas">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                            </svg>
                            Terbitkan Tugas
                        </button>
                    </form>
                @endif
                <a
                    href="{{ route('assignments.edit', $assignment) }}"
                    class="btn-secondary text-xs inline-flex items-center gap-2"
                >
                    <svg class="w-4 h-4 text-slate-700" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                    </svg>
                    Edit Tugas
                </a>
                @can('delete', $assignment)
                    <form action="{{ route('assignments.destroy', $assignment) }}" method="POST" onsubmit="return confirm('Hapus tugas ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger-soft text-xs py-2 px-3">Hapus Tugas</button>
                    </form>
                @endcan
            </div>
        @endcan
    </div>



    @if ($errors->any())
        <div class="mb-6 p-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium space-y-1">
            <div class="flex items-center gap-2 font-semibold">
                <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>Terjadi kendala pada formulir:</span>
            </div>
            <ul class="list-disc list-inside pl-5 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

        {{-- Grid utama: header + isi --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Header tugas --}}
            <div class="lms-card p-6 sm:p-7">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-4">
                    <div class="min-w-0">
                        <h1 class="text-xl font-bold text-slate-900 tracking-tight leading-snug break-words">
                            {{ $assignment->title }}
                        </h1>
                        <p class="text-sm text-slate-600 mt-1.5 font-normal">
                            Mata kuliah: <a href="{{ route('courses.show', $assignment->course) }}" class="font-semibold text-slate-800 hover:underline">{{ $assignment->course->name }}</a>
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 shrink-0">
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold rounded-md border border-slate-200 bg-slate-100 text-slate-900 px-2.5 py-1.5 font-mono">
                            <svg class="w-3.5 h-3.5 text-slate-600" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            Tenggat: {{ $assignment->due_at->format('d M Y, H:i') }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold rounded-md border border-slate-200 bg-slate-100 text-slate-900 px-2.5 py-1.5 font-mono">
                            <svg class="w-3.5 h-3.5 text-slate-600" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                            Maks: {{ $assignment->max_score }} pts
                        </span>
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-5 space-y-3">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">
                        Instruksi Pengerjaan
                    </h2>
                    <div class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">
                        {{ $assignment->instructions ?: 'Tidak ada instruksi tambahan.' }}
                    </div>

                    <div class="pt-3 flex flex-wrap items-center gap-3 text-xs text-slate-500 border-t border-slate-100">
                        <span>Peserta kelas: <strong class="text-slate-700">{{ $assignment->course->students_count ?? ($assignment->course->students?->count() ?? 0) }} mahasiswa</strong></span>
                        @if ($assignment->allow_late)
                            <span class="text-amber-700 font-medium">· Menerima keterlambatan</span>
                        @else
                            <span class="text-rose-600 font-medium">· Tidak menerima keterlambatan</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Role-aware actions --}}
            @auth
                @if (auth()->user()->role === 'mahasiswa' || auth()->user()->role === 'student')
                    @php
                        $user = auth()->user();
                        $isEnrolled = $assignment->course->hasStudent($user);
                        $submission = $mySubmission ?? ($assignment->submissions()->where('user_id', $user->id)->first());
                        $isPastDue = now()->gt($assignment->due_at);
                        $canSubmit = $isEnrolled && (! $isPastDue || $assignment->allow_late);
                    @endphp

                    <div class="lms-card p-6 space-y-6">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                            <div>
                                <h2 class="text-sm font-bold text-slate-900">Status Pengumpulan Anda</h2>
                                <p class="text-xs text-slate-500 mt-0.5">Pantau status pengerjaan dan berkas jawaban tugas Anda</p>
                            </div>
                            @if ($submission)
                                @if ($submission->is_late)
                                    <span class="status status-late"><span class="status-dot" aria-hidden="true"></span>Dikumpulkan Terlambat</span>
                                @else
                                    <span class="status status-ontime"><span class="status-dot" aria-hidden="true"></span>Dikumpulkan Tepat Waktu</span>
                                @endif
                            @else
                                @if ($isPastDue && ! $assignment->allow_late)
                                    <span class="status status-late"><span class="status-dot" aria-hidden="true"></span>Pengumpulan Ditutup</span>
                                @else
                                    <span class="status status-draft"><span class="status-dot" aria-hidden="true"></span>Belum Mengumpulkan</span>
                                @endif
                            @endif
                        </div>

                        {{-- Kartu Riwayat Pengumpulan Jika Sudah Ada (Netral Kontras Tinggi, Tanpa Blok Pastel) --}}
                        @if ($submission)
                            <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-5 space-y-4">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-10 h-10 rounded-lg bg-white border border-slate-200 text-slate-900 flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Berkas Jawaban Terkumpul</p>
                                            <p class="text-sm font-bold text-slate-900 truncate mt-0.5">{{ $submission->original_name }}</p>
                                            <p class="text-[11px] text-slate-600 font-mono mt-0.5">
                                                {{ $submission->file_size ? \Illuminate\Support\Number::fileSize($submission->file_size) : '-' }} ·
                                                Diserahkan {{ $submission->submitted_at?->format('d M Y, H:i') ?? $submission->created_at->format('d M Y, H:i') }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 shrink-0">
                                        <a href="{{ route('submissions.download', $submission) }}" class="btn-primary text-xs py-2 px-3.5 inline-flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                            </svg>
                                            <span>Unduh Berkas</span>
                                        </a>
                                        <a href="{{ route('submissions.show', $submission) }}" class="btn-outline text-xs py-2 px-3">
                                            Lihat Detail
                                        </a>
                                    </div>
                                </div>

                                @if ($submission->note)
                                    <div class="pt-3 border-t border-slate-200/80 text-xs">
                                        <span class="text-slate-600 font-semibold block mb-1">Catatan Mahasiswa:</span>
                                        <p class="text-slate-800 bg-white p-3 rounded-lg border border-slate-200 whitespace-pre-line">{{ $submission->note }}</p>
                                    </div>
                                @endif

                                @if ($submission->grade)
                                    @can('view', $submission->grade)
                                        <div class="pt-3 border-t border-slate-200/80 flex items-center justify-between text-xs">
                                            <span class="font-semibold text-slate-700">Hasil Penilaian Dosen:</span>
                                            <span class="font-mono font-bold text-sm text-slate-900 bg-white px-3 py-1 rounded border border-slate-200">
                                                {{ $submission->grade->score }} / {{ $assignment->max_score }} pts
                                            </span>
                                        </div>
                                        @if ($submission->grade->feedback)
                                            <div class="text-xs bg-white p-3 rounded-lg border border-slate-200">
                                                <span class="font-semibold text-slate-900 block mb-1">Catatan Evaluasi & Umpan Balik:</span>
                                                <p class="text-slate-700 leading-relaxed">{{ $submission->grade->feedback }}</p>
                                            </div>
                                        @endif
                                    @endcan
                                @endif
                            </div>
                        @endif

                        {{-- Formulir Unggah / Pengumpulan Ulang --}}
                        @if (! $isEnrolled)
                            <div class="p-4 rounded-lg bg-rose-50 border border-rose-200 text-xs text-rose-800">
                                Anda tidak terdaftar sebagai peserta pada mata kuliah ini, sehingga tidak dapat mengumpulkan tugas.
                            </div>
                        @elseif ($isPastDue && ! $assignment->allow_late && ! $submission)
                            <div class="p-4 rounded-lg bg-rose-50 border border-rose-200 text-xs text-rose-800 flex items-center gap-2">
                                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span>Batas waktu pengumpulan tugas ini telah berakhir pada <strong>{{ $assignment->due_at->format('d M Y, H:i') }}</strong> dan pengajar tidak mengizinkan pengumpulan terlambat.</span>
                            </div>
                        @else
                            <div class="border-t border-slate-100 pt-5">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                                    {{ $submission ? 'Perbarui Pengumpulan Berkas' : 'Formulir Pengumpulan Tugas' }}
                                </h3>

                                @if ($submission)
                                    <p class="text-xs text-slate-500 mb-4">
                                        Mengunggah berkas baru akan <strong class="text-slate-800">menggantikan berkas tugas sebelumnya</strong> secara otomatis.
                                    </p>
                                @endif

                                @if ($isPastDue && $assignment->allow_late)
                                    <div class="mb-4 p-3 rounded-lg bg-amber-50 border border-amber-200 text-xs text-amber-800 flex items-center gap-2">
                                        <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>Perhatian: Waktu sekarang telah melewati batas tenggat. Pengumpulan Anda akan ditandai sebagai <strong>Terlambat</strong>.</span>
                                    </div>
                                @endif

                                <form action="{{ route('assignments.submissions.store', $assignment) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                    @csrf

                                    <div>
                                        <label for="submission_file" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                            Pilih Berkas Jawaban (PDF, DOC, DOCX) <span class="text-rose-600">*</span>
                                        </label>
                                        <div class="mt-1 flex justify-center px-4 py-4 border border-dashed border-slate-300 rounded-lg hover:border-slate-900 transition-colors bg-slate-50/50">
                                            <div class="space-y-1.5 text-center w-full">
                                                <input
                                                    id="submission_file"
                                                    name="file"
                                                    type="file"
                                                    accept=".pdf,.doc,.docx"
                                                    required
                                                    aria-describedby="file-constraints"
                                                    aria-errormessage="file-error"
                                                    class="block w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3.5 file:rounded-md file:border file:border-slate-300 file:text-xs file:font-semibold file:bg-white file:text-slate-900 hover:file:bg-slate-100 cursor-pointer"
                                                />
                                                <p id="file-constraints" class="text-[11px] text-slate-500">Maksimal ukuran berkas: <strong class="text-slate-700">5 MB</strong>.</p>
                                            </div>
                                        </div>

                                        {{-- Client-side inspection feedback (Modern Web Guidance: validate-input-after-interaction) --}}
                                        <div id="file-client-preview" class="hidden mt-2 p-2.5 rounded-lg bg-slate-100 border border-slate-200 text-xs text-slate-900 flex items-center justify-between">
                                            <div class="flex items-center gap-1.5 min-w-0">
                                                <svg class="w-4 h-4 text-slate-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <span id="file-client-name" class="font-semibold truncate max-w-[200px]"></span>
                                            </div>
                                            <span id="file-client-size" class="text-[11px] text-slate-700 shrink-0 font-mono font-semibold"></span>
                                        </div>

                                        <div id="file-client-error" class="hidden mt-2 p-2.5 rounded bg-rose-50 border border-rose-200 text-xs text-rose-700 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                            <span id="file-client-error-text"></span>
                                        </div>

                                        @error('file')
                                            <p id="file-error" class="mt-1.5 text-xs text-rose-700 font-medium">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="submission_note" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                            Catatan untuk Dosen (Opsional)
                                        </label>
                                        <textarea
                                            id="submission_note"
                                            name="note"
                                            rows="2"
                                            placeholder="Tuliskan keterangan singkat mengenai pengumpulan Anda jika diperlukan..."
                                            class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-900 placeholder-slate-400 ring-focus transition-shadow focus:border-sky-600 focus:ring-sky-600 resize-y"
                                        >{{ old('note') }}</textarea>
                                        @error('note')
                                            <p class="mt-1 text-xs text-rose-700">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="flex justify-end pt-2">
                                        <button type="submit" class="btn-primary text-xs py-2 px-4 inline-flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                                            </svg>
                                            <span>{{ $submission ? 'Perbarui Tugas' : 'Kumpulkan Tugas' }}</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endif
                    </div>
                @elseif (auth()->user()->role === 'dosen' || auth()->user()->role === 'admin')
                    @php
                        $totalStudents = $assignment->course->students_count ?? ($assignment->course->students?->count() ?? 0);
                        $submissionsByStudent = $submissions->keyBy('user_id');
                    @endphp

                    <div class="lms-card p-6 space-y-5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                            <div>
                                <h2 class="text-sm font-bold text-slate-900">
                                    Rekap Pengumpulan Peserta Kelas
                                </h2>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Daftar mahasiswa terdaftar dan status evaluasi tugas
                                </p>
                            </div>
                            
                            {{-- Ringkasan statistik ringkas (Distill: menghilangkan kartu bertumpuk) --}}
                            <div class="flex flex-wrap items-center gap-2 text-xs font-mono">
                                <span class="px-2.5 py-1 rounded-md bg-slate-100 text-slate-700 font-semibold">
                                    Total: {{ $totalStudents }}
                                </span>
                                <span class="px-2.5 py-1 rounded-md bg-sky-50 text-sky-800 font-semibold border border-sky-100">
                                    Terkumpul: {{ $submittedCount }}
                                </span>
                                <span class="px-2.5 py-1 rounded-md bg-rose-50 text-rose-800 font-semibold border border-rose-100">
                                    Belum: {{ max(0, $totalStudents - $submittedCount) }}
                                </span>
                                <span class="px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-800 font-semibold border border-emerald-100">
                                    Dinilai: {{ $submissions->whereNotNull('grade')->count() }}
                                </span>
                            </div>
                        </div>

                        {{-- Tabel Daftar Pengumpulan Mahasiswa (Modern Web Guidance: responsive-table) --}}
                        @if ($enrolledStudents->isNotEmpty())
                            <div class="overflow-x-auto border border-slate-200 rounded-lg focus:ring-2 focus:ring-sky-500 outline-none" tabindex="0" role="region" aria-label="Tabel Rekap Pengumpulan Tugas Mahasiswa">
                                <table class="w-full text-left text-xs text-slate-700">
                                    <thead class="sticky top-0 z-10 bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold uppercase text-[10px] tracking-wider shadow-sm">
                                        <tr>
                                            <th class="py-2.5 px-3">Mahasiswa</th>
                                            <th class="py-2.5 px-3">Status</th>
                                            <th class="py-2.5 px-3">Waktu Kumpul</th>
                                            <th class="py-2.5 px-3">Berkas</th>
                                            <th class="py-2.5 px-3">Nilai</th>
                                            <th class="py-2.5 px-3 text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 bg-white">
                                        @foreach ($enrolledStudents as $student)
                                            @php
                                                $sub = $submissionsByStudent->get($student->id);
                                            @endphp
                                            <tr class="hover:bg-slate-50/70 transition-colors">
                                                <td class="py-2.5 px-3">
                                                    <div class="font-semibold text-slate-900">{{ $student->name }}</div>
                                                    <div class="text-[10px] font-mono text-slate-500">{{ $student->nim_nip ?: $student->email }}</div>
                                                </td>
                                                <td class="py-2.5 px-3">
                                                    @if ($sub)
                                                        @if ($sub->is_late)
                                                            <span class="status status-late"><span class="status-dot"></span>Terlambat</span>
                                                        @else
                                                            <span class="status status-ontime"><span class="status-dot"></span>Tepat Waktu</span>
                                                        @endif
                                                    @else
                                                        <span class="status status-draft"><span class="status-dot"></span>Belum Kumpul</span>
                                                    @endif
                                                </td>
                                                <td class="py-2.5 px-3 font-mono text-[11px] text-slate-600">
                                                    {{ $sub?->submitted_at?->format('d M, H:i') ?? '-' }}
                                                </td>
                                                <td class="py-2.5 px-3">
                                                    @if ($sub && $sub->file_path)
                                                        <a href="{{ route('submissions.download', $sub) }}" class="inline-flex items-center gap-1 font-semibold text-sky-700 hover:text-sky-900 hover:underline" title="{{ $sub->original_name }}">
                                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                                            </svg>
                                                            <span class="truncate max-w-[120px]">{{ $sub->original_name }}</span>
                                                        </a>
                                                    @else
                                                        <span class="text-slate-400 italic">-</span>
                                                    @endif
                                                </td>
                                                <td class="py-2.5 px-3 font-mono font-semibold">
                                                    @if ($sub?->grade)
                                                        <span class="text-slate-900">{{ $sub->grade->score }}</span>
                                                        <span class="text-slate-400 font-normal">/{{ $assignment->max_score }}</span>
                                                    @elseif ($sub)
                                                        <span class="text-amber-600 font-normal text-[11px]">Belum Dinilai</span>
                                                    @else
                                                        <span class="text-slate-400 font-normal">-</span>
                                                    @endif
                                                </td>
                                                <td class="py-2.5 px-3 text-right">
                                                    @if ($sub)
                                                        <a href="{{ route('submissions.show', $sub) }}" class="btn-outline text-[11px] py-1 px-2.5">
                                                            {{ $sub->grade ? 'Ubah Nilai' : 'Beri Nilai' }}
                                                        </a>
                                                    @else
                                                        <span class="text-slate-300 text-[11px]">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="p-6 text-center text-xs text-slate-500 bg-slate-50 rounded-lg border border-slate-200">
                                Belum ada mahasiswa yang terdaftar di kelas ini.
                            </div>
                        @endif
                    </div>
                @endif
            @endauth
        </div>

        {{-- Samping: informasi tambahan & metadata tugas --}}
        <div class="space-y-6">
            <div class="lms-card p-6">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-4">
                    Informasi Penugasan
                </h3>

                <dl class="space-y-3 text-xs">
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-500 font-normal">Mata Kuliah</dt>
                        <dd class="text-slate-900 font-semibold text-right">
                            <a href="{{ route('courses.show', $assignment->course) }}" class="hover:underline">
                                {{ $assignment->course->code }}
                            </a>
                        </dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-500 font-normal">Pengampu</dt>
                        <dd class="text-slate-900 font-medium text-right">
                            {{ $assignment->course->lecturer?->name ?? 'Dosen Pengampu' }}
                        </dd>
                    </div>
                    <div class="flex justify-between items-center gap-2">
                        <dt class="text-slate-500 font-normal">Status</dt>
                        <dd class="text-right">
                            <span class="status {{ ($assignment->status ?? 'draft') === 'published' ? 'status-active' : 'status-draft' }}">
                                <span class="status-dot" aria-hidden="true"></span>{{ ucfirst($assignment->status ?? 'draft') }}
                            </span>
                        </dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-500 font-normal">Kebijakan</dt>
                        <dd class="text-slate-900 text-right">
                            {{ $assignment->allow_late ? 'Terlambat diterima' : 'Tepat waktu' }}
                        </dd>
                    </div>
                </dl>

                <div class="mt-5 pt-4 border-t border-slate-100 text-[11px] text-slate-500 space-y-1.5">
                    <div class="flex justify-between">
                        <span>Diterbitkan</span>
                        <span class="font-mono text-slate-700">{{ $assignment->created_at->format('d M Y, H:i') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Terakhir diubah</span>
                        <span class="font-mono text-slate-700">{{ $assignment->updated_at->format('d M Y, H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Modern Web Guidance: validate-input-after-interaction & accessible-error-announcement --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const fileInput = document.getElementById('submission_file');
            const previewEl = document.getElementById('file-client-preview');
            const previewName = document.getElementById('file-client-name');
            const previewSize = document.getElementById('file-client-size');
            const errorEl = document.getElementById('file-client-error');
            const errorText = document.getElementById('file-client-error-text');

            if (fileInput) {
                const MAX_BYTES = 5 * 1024 * 1024; // 5 MB
                const ALLOWED_EXTS = ['.pdf', '.doc', '.docx'];

                const formatBytes = (bytes) => {
                    if (bytes === 0) return '0 B';
                    const k = 1024;
                    const sizes = ['B', 'KB', 'MB', 'GB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                };

                const syncAria = (el, isInvalid) => {
                    el.setAttribute('aria-invalid', isInvalid ? 'true' : 'false');
                };

                fileInput.addEventListener('change', () => {
                    const file = fileInput.files && fileInput.files[0];
                    if (!file) {
                        previewEl?.classList.add('hidden');
                        errorEl?.classList.add('hidden');
                        fileInput.removeAttribute('aria-invalid');
                        return;
                    }

                    const fileName = file.name;
                    const ext = '.' + fileName.split('.').pop().toLowerCase();

                    // 1. Validasi ekstensi klien
                    if (!ALLOWED_EXTS.includes(ext)) {
                        previewEl?.classList.add('hidden');
                        errorEl?.classList.remove('hidden');
                        if (errorText) errorText.textContent = `Format berkas "${ext}" tidak diizinkan. Gunakan format PDF, DOC, atau DOCX.`;
                        syncAria(fileInput, true);
                        fileInput.value = '';
                        return;
                    }

                    // 2. Validasi ukuran klien (Maks 5 MB)
                    if (file.size > MAX_BYTES) {
                        previewEl?.classList.add('hidden');
                        errorEl?.classList.remove('hidden');
                        if (errorText) errorText.textContent = `Ukuran berkas (${formatBytes(file.size)}) melebihi batas maksimal 5 MB.`;
                        syncAria(fileInput, true);
                        fileInput.value = '';
                        return;
                    }

                    // 3. Valid feedback
                    errorEl?.classList.add('hidden');
                    if (previewName) previewName.textContent = fileName;
                    if (previewSize) previewSize.textContent = formatBytes(file.size);
                    previewEl?.classList.remove('hidden');
                    syncAria(fileInput, false);
                });

                fileInput.addEventListener('blur', () => {
                    if (!fileInput.value && fileInput.hasAttribute('required')) {
                        syncAria(fileInput, true);
                    }
                });
            }
        });
    </script>

</x-layout>
