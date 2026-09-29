<x-layout title="Detail Pengumpulan: {{ $submission->assignment->title }}">
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <a href="{{ url()->previous() ?: route('dashboard') }}" class="btn-outline text-xs py-2 inline-flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                <span>Kembali</span>
            </a>
            <span class="text-xs font-mono px-2.5 py-1 rounded bg-slate-100 text-slate-700">
                Peran: {{ ucfirst($activeRole) }}
            </span>
        </div>

        <div class="lms-card p-6 sm:p-8 space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    {{ $submission->assignment->course->code ?? 'KULIAH' }} — {{ $submission->assignment->course->name ?? '' }}
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
                        <span class="badge-coral text-[10px] ml-1">Terlambat</span>
                    @else
                        <span class="badge-mint text-[10px] ml-1">Tepat Waktu</span>
                    @endif
                </div>
            </div>

            @if ($submission->note)
                <div class="bg-slate-50 p-4 rounded-lg border border-slate-100">
                    <span class="text-xs font-medium text-slate-500 block mb-1">Catatan Mahasiswa:</span>
                    <p class="text-sm text-slate-700 whitespace-pre-line">{{ $submission->note }}</p>
                </div>
            @endif

            <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Berkas: <strong class="text-slate-800">{{ $submission->original_name ?? 'submission-file' }}</strong></span>
                <span>Nilai: <strong class="text-slate-900">{{ $submission->grade?->score ?? 'Belum Dinilai' }}</strong> / {{ $submission->assignment->max_score ?? 100 }}</span>
            </div>
        </div>
    </div>
</x-layout>
