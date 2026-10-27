<x-layout title="Detail Pengumpulan: {{ $submission->assignment->title }}">
    @can('view', $submission)
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <a href="{{ url()->previous() ?: route('dashboard') }}" class="btn-outline text-xs py-2 inline-flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                <span>Kembali</span>
            </a>
        </div>

        @if (session('success'))
            <div class="p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <div class="lms-card p-6 sm:p-8 space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <p class="text-xs font-medium text-slate-600 font-mono">
                    {{ $submission->assignment->course->code ?? 'KULIAH' }} <span aria-hidden="true">·</span> {{ $submission->assignment->course->name ?? '' }}
                </p>
                <h1 class="text-2xl font-bold text-slate-900 mt-1">
                    {{ $submission->assignment->title }}
                </h1>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-slate-500 text-xs block">Mahasiswa</span>
                    <strong class="text-slate-900">{{ $submission->student->name ?? 'Mahasiswa' }}</strong>
                    <p class="text-xs text-slate-500">{{ $submission->student->email ?? '' }}</p>
                </div>
                <div>
                    <span class="text-slate-500 text-xs block">Waktu Pengumpulan</span>
                    <strong class="text-slate-900">{{ $submission->submitted_at?->format('d M Y, H:i') ?? '-' }}</strong>
                    @if ($submission->is_late)
                        <span class="status status-late ml-1"><span class="status-dot" aria-hidden="true"></span>Terlambat</span>
                    @else
                        <span class="status status-ontime ml-1"><span class="status-dot" aria-hidden="true"></span>Tepat waktu</span>
                    @endif
                </div>
            </div>

            @if ($submission->note)
                <div class="pt-4 border-t border-slate-100 space-y-1.5">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Catatan Mahasiswa</h2>
                    <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">{{ $submission->note }}</p>
                </div>
            @endif

            <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-sky-700" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                    <span>Berkas Jawaban: <strong class="text-slate-800">{{ $submission->original_name ?? 'submission-file.pdf' }}</strong> ({{ $submission->file_size ? \Illuminate\Support\Number::fileSize($submission->file_size) : '-' }})</span>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('submissions.download', $submission) }}" class="btn-primary text-xs py-1.5 px-3 inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        <span>Unduh Berkas</span>
                    </a>
                </div>
            </div>

            <div class="flex items-center justify-between text-xs text-slate-500 pt-2">
                <span>Status Penilaian:</span>
                <span>
                    @if ($submission->grade)
                        @can('view', $submission->grade)
                            <strong class="text-slate-900 font-semibold text-sm">{{ $submission->grade->score }}</strong> / {{ $submission->assignment->max_score ?? 100 }} pts
                        @else
                            <strong class="text-slate-900">Belum dapat dilihat</strong>
                        @endcan
                    @else
                        <strong class="text-slate-900">Belum Dinilai</strong> / {{ $submission->assignment->max_score ?? 100 }} pts
                    @endif
                </span>
            </div>

            @if ($submission->grade && $submission->grade->feedback)
                <div class="pt-4 border-t border-slate-100 space-y-1.5">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Catatan & Umpan Balik Dosen</h2>
                    <p class="text-xs text-slate-700 leading-relaxed whitespace-pre-line">{{ $submission->grade->feedback }}</p>
                </div>
            @endif

            {{-- Formulir Input Nilai (Khusus Dosen Pengampu & Admin) --}}
            @if (in_array($activeRole, ['dosen', 'admin']))
                <div class="pt-6 border-t border-slate-100 space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-bold text-slate-900">
                            {{ $submission->grade ? 'Perbarui Penilaian Dosen' : 'Formulir Penilaian Dosen' }}
                        </h2>
                        <span class="text-xs text-slate-500 font-mono">Nilai maks: {{ $submission->assignment->max_score ?? 100 }} pts</span>
                    </div>

                    <form action="{{ route('submissions.grade.store', $submission) }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label for="score" class="block text-xs font-semibold text-slate-700 mb-1">
                                    Skor Nilai <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    max="{{ $submission->assignment->max_score ?? 100 }}"
                                    name="score"
                                    id="score"
                                    value="{{ old('score', $submission->grade?->score) }}"
                                    required
                                    placeholder="Contoh: 85"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs font-mono text-slate-900 focus:outline-none focus:border-slate-900"
                                >
                            </div>
                            <div class="sm:col-span-2">
                                <label for="feedback" class="block text-xs font-semibold text-slate-700 mb-1">
                                    Catatan / Umpan Balik (Opsional)
                                </label>
                                <input
                                    type="text"
                                    name="feedback"
                                    id="feedback"
                                    value="{{ old('feedback', $submission->grade?->feedback) }}"
                                    placeholder="Tuliskan catatan evaluasi pengerjaan untuk mahasiswa..."
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs text-slate-900 focus:outline-none focus:border-slate-900"
                                >
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="btn-primary text-xs py-2 px-4">
                                {{ $submission->grade ? 'Simpan Perubahan Nilai' : 'Simpan Penilaian' }}
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>

        @can('create', [\App\Models\Grade::class, $submission])
            <form action="{{ route('submissions.grade.store', $submission) }}" method="POST" class="lms-card space-y-4 p-6 sm:p-8">
                @csrf
                <div>
                    <h2 class="text-base font-bold text-slate-900">Penilaian</h2>
                    <p class="mt-1 text-xs text-slate-600">Nilai dan feedback ini hanya bisa dilihat mahasiswa pemilik pengumpulan.</p>
                </div>

                <label class="block text-sm font-semibold text-slate-800">
                    Nilai (maksimal {{ $submission->assignment->max_score }})
                    <input type="number" name="score" min="0" max="{{ $submission->assignment->max_score }}" step="0.01" required value="{{ old('score', $submission->grade?->score) }}" class="mt-1 block w-full rounded-md border-slate-300 text-sm focus:border-slate-700 focus:ring-slate-700">
                    @error('score') <span class="mt-1 block text-xs font-normal text-rose-700">{{ $message }}</span> @enderror
                </label>

                <label class="block text-sm font-semibold text-slate-800">
                    Feedback
                    <textarea name="feedback" rows="4" maxlength="2000" class="mt-1 block w-full rounded-md border-slate-300 text-sm focus:border-slate-700 focus:ring-slate-700">{{ old('feedback', $submission->grade?->feedback) }}</textarea>
                    @error('feedback') <span class="mt-1 block text-xs font-normal text-rose-700">{{ $message }}</span> @enderror
                </label>

                <button type="submit" class="btn-primary text-xs">Simpan nilai</button>
            </form>
        @endcan
    </div>
    @endcan
</x-layout>
