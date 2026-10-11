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
            <span class="text-xs font-mono px-2.5 py-1 rounded-md bg-slate-100 text-slate-700">
                Peran: {{ ucfirst($activeRole) }}
            </span>
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
                <div class="bg-slate-50 p-4 rounded-lg border border-slate-100">
                    <span class="meta-label block mb-1">Catatan mahasiswa</span>
                    <p class="text-sm text-slate-700 whitespace-pre-line">{{ $submission->note }}</p>
                </div>
            @endif

            <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Berkas: <strong class="text-slate-800">{{ $submission->original_name ?? 'submission-file' }}</strong></span>
                <span>Nilai:
                    @if ($submission->grade)
                        @can('view', $submission->grade)
                            <strong class="text-slate-900 font-semibold">{{ $submission->grade->score }}</strong> / {{ $submission->assignment->max_score ?? 100 }}
                        @else
                            <strong class="text-slate-900">Belum dapat dilihat</strong>
                        @endcan
                    @else
                        <strong class="text-slate-900">Belum Dinilai</strong> / {{ $submission->assignment->max_score ?? 100 }}
                    @endif
                </span>
            </div>

            @if ($submission->grade && $submission->grade->feedback)
                <div class="p-4 rounded-lg bg-slate-50 border border-slate-200">
                    <span class="text-xs font-semibold text-slate-800 block mb-1">Catatan & Umpan Balik Dosen:</span>
                    <p class="text-xs text-slate-700 leading-relaxed">{{ $submission->grade->feedback }}</p>
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
    </div>
    @endcan
</x-layout>
