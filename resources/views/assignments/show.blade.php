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
                <a
                    href="{{ route('dosen.courses.assignments.edit', [$assignment->course, $assignment]) }}"
                    class="btn-secondary text-xs inline-flex items-center gap-2"
                >
                    <svg class="w-4 h-4 text-slate-700" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                    </svg>
                    Edit Tugas
                </a>
            </div>
        @endcan
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

        {{-- Grid utama: header + isi --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Header tugas --}}
            <div class="lms-card p-6 sm:p-7">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-4">
                    <div class="min-w-0">
                        <h1 class="text-xl font-bold text-slate-900 tracking-tight leading-snug break-words">
                            {{ $assignment->title }}
                        </h1>
                        <p class="text-sm text-slate-600 mt-1.5 font-normal">
                            Mata kuliah: <span class="font-semibold text-slate-800">{{ $assignment->course->name }}</span>
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 shrink-0">
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold rounded-md border border-slate-200 bg-sky-50/70 text-sky-800 px-2.5 py-1.5">
                            <svg class="w-3.5 h-3.5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            Batas: {{ $assignment->due_at->format('d M Y, H:i') }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold rounded-md border border-slate-200 bg-slate-100 text-slate-800 px-2.5 py-1.5">
                            <svg class="w-3.5 h-3.5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                            Nilai maksimal: {{ $assignment->max_score }} pts
                        </span>
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-4 space-y-4">
                    <div>
                        <h2 class="text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                            Instruksi Pengerjaan
                        </h2>
                        <div class="rounded-lg border border-slate-200 bg-slate-50/70 px-4 py-3.5 text-sm text-slate-700 leading-relaxed">
                            @if ($assignment->instructions)
                                {{ $assignment->instructions }}
                            @else
                                <span class="italic text-slate-400">Tidak ada instruksi tambahan.</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500">
                        <span>Dihadiri oleh
                            <strong class="text-slate-700 font-semibold">{{ $assignment->course->students_count ?? ($assignment->course->students?->count() ?? 0) }}</strong>
                            peserta
                        </span>
                        @if ($assignment->allow_late)
                            <span class="inline-flex items-center gap-1 rounded-md border border-slate-200 bg-amber-50/70 text-amber-800 px-2.5 py-1">
                                <svg class="w-3.5 h-3.5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                Terlambat diizinkan
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Role-aware actions --}}
            @auth
                @if (auth()->user()->role === 'mahasiswa' || auth()->user()->role === 'student')
                    @php
                        $submission = $assignment->submissions()
                            ->where('user_id', auth()->id())
                            ->first();
                    @endphp

                    <div class="lms-card p-6">
                        <h2 class="text-sm font-bold text-slate-900 mb-4">
                            Status Pengumpulan
                        </h2>

                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                @if ($submission)
                                    <div class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-[#EDF3EC] text-[#245228] text-xs font-semibold px-3 py-2 mb-2">
                                        <svg class="w-4 h-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                        Sudah dikumpulkan
                                    </div>
                                    <p class="text-sm text-slate-700 font-normal">
                                        Upload: <span class="font-mono font-semibold text-slate-900">
                                            {{ $submission->created_at->format('d M Y, H:i') }}
                                        </span>
                                    </p>
                                    @if ($submission->grade)
                                        <p class="text-sm text-slate-700 mt-1">
                                            Nilai: <span class="font-mono font-bold text-slate-900">
                                                {{ $submission->grade->score ?? '-' }}
                                            </span>
                                            / {{ $assignment->max_score }}
                                        </p>
                                    @endif
                                @else
                                    <div class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 text-slate-700 text-xs font-semibold px-3 py-2 mb-2">
                                        <svg class="w-4 h-4 text-slate-500" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                                        </svg>
                                        Belum dikumpulkan
                                    </div>
                                    <p class="text-sm text-slate-600 font-normal mt-1">
                                        Anda belum mengumpulkan tugas ini.
                                    </p>
                                @endif
                            </div>

                            <div class="flex-shrink-0">
                                @if (!$submission)
                                    @can('create', \App\Models\Submission::class)
                                        <form action="{{ route('dosen.courses.assignments.scoped-show', [$assignment->course, 'assignment' => $assignment]) }}" method="POST" class="inline">
                                            @csrf
                                            <button
                                                type="submit"
                                                class="btn-primary text-xs inline-flex items-center gap-2"
                                            >
                                                <svg class="w-4 h-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                                                </svg>
                                                Kumpulkan Tugas
                                            </button>
                                        </form>
                                    @endcan
                                @else
                                    <a
                                        href="{{ route('submissions.show', $submission) }}"
                                        class="btn-secondary text-xs inline-flex items-center gap-2"
                                    >
                                        <svg class="w-4 h-4 text-slate-700" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                        </svg>
                                        Lihat pengiriman
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @elseif (auth()->user()->role === 'dosen' || auth()->user()->role === 'admin')
                    @php
                        $submittedCount = $assignment->submissions()->distinct()->count('user_id');
                        $totalStudents = $assignment->course->students_count ?? ($assignment->course->students?->count() ?? 0);
                    @endphp

                    <div class="lms-card p-6">
                        <h2 class="text-sm font-bold text-slate-900 mb-4">
                            Ringkasan Pengumpulan
                        </h2>

                        <div class="flex flex-wrap items-center gap-4 mb-4">
                            <div class="rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm">
                                <span class="text-slate-600 font-normal">Sudah mengumpulkan</span>
                                <div class="mt-1 flex items-baseline gap-1">
                                    <span class="text-2xl font-bold text-slate-900">{{ $submittedCount }}</span>
                                    <span class="text-slate-500 font-normal">/ {{ $totalStudents }} peserta</span>
                                </div>
                            </div>
                            <div class="rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm">
                                <span class="text-slate-600 font-normal">Belum mengumpulkan</span>
                                <div class="mt-1 flex items-baseline gap-1">
                                    <span class="text-2xl font-bold text-slate-900">{{ max(0, $totalStudents - $submittedCount) }}</span>
                                    <span class="text-slate-500 font-normal">peserta</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <a
                                href="{{ route('dosen.courses.assignments.scoped-show', [$assignment->course, 'assignment' => $assignment]) }}"
                                class="btn-primary text-xs inline-flex items-center gap-2"
                            >
                                <svg class="w-4 h-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.094-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801-.372-.312.312c1.541.184 3.14.28 4.748.28 1.608 0 3.216-.096 4.752-.28l.312.312c1.568.18 3.26.33 5.043.428a9.563 9.563 0 0 1-2.124 10.21c-1.621.35-3.34.523-5.098.523-1.758 0-3.476-.173-5.182-.52a9.828 9.828 0 0 1-2.123-10.21c1.783-.098 3.474-.248 5.043-.428Z" />
                                </svg>
                                Lihat & Nilai Pengumpulan
                            </a>
                            <a
                                href="{{ route('courses.show', $assignment->course) }}#assignments"
                                class="btn-outline text-xs inline-flex items-center gap-2"
                            >
                                Kembali ke daftar tugas
                            </a>
                        </div>
                    </div>
                @endif
            @endauth
        </div>

        {{-- Samping: metadata tugas --}}
        <div class="space-y-6">
            <div class="lms-card p-6">
                <h3 class="text-xs font-bold text-slate-600 uppercase tracking-wider mb-4">
                    Info Tugas
                </h3>

                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-600 font-normal">Judul</dt>
                        <dd class="text-slate-900 font-semibold text-right break-words">{{ $assignment->title }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-600 font-normal">Batas waktu</dt>
                        <dd class="text-slate-900 font-mono text-right">{{ $assignment->due_at->format('d M Y, H:i') }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-600 font-normal">Nilai maksimal</dt>
                        <dd class="text-slate-900 font-mono text-right">{{ $assignment->max_score }} pts</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-600 font-normal">Status</dt>
                        <dd class="text-right">
                            <span class="inline-flex items-center gap-1 rounded-md border border-slate-200 bg-slate-100 text-slate-800 text-[11px] font-semibold px-2 py-0.5 uppercase">
                                {{ ucfirst($assignment->status ?? 'draft') }}
                            </span>
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-600 font-normal">Peserta</dt>
                        <dd class="text-slate-900 font-mono text-right">{{ $totalStudents ?? '-' }}</dd>
                    </div>
                </dl>

                <div class="mt-5 pt-4 border-t border-slate-100 text-xs text-slate-500 space-y-1.5">
                    <div class="flex justify-between">
                        <span>Dibuat</span>
                        <span class="font-mono text-slate-700">{{ $assignment->created_at->format('d M Y, H:i') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Diperbarui</span>
                        <span class="font-mono text-slate-700">{{ $assignment->updated_at->format('d M Y, H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

</x-layout>
