<x-layout title="Nilai Saya">
    <div class="mx-auto max-w-5xl space-y-5">
        <div>
            <p class="text-xs font-semibold uppercase text-slate-500">Hasil penilaian</p>
            <h1 class="mt-1 text-xl font-bold text-slate-900">Nilai Saya</h1>
        </div>

        <div class="divide-y divide-slate-200 border-y border-slate-200">
            @forelse ($grades as $grade)
                <article class="py-4">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h2 class="text-sm font-semibold text-slate-900">{{ $grade->submission->assignment->title }}</h2>
                            <p class="mt-1 text-xs text-slate-600">{{ $grade->submission->assignment->course->name }}</p>
                        </div>
                        <p class="font-mono text-sm font-bold text-slate-900">{{ $grade->score }} / {{ $grade->submission->assignment->max_score }}</p>
                    </div>
                    @if ($grade->feedback)
                        <p class="mt-3 whitespace-pre-line text-sm text-slate-700">{{ $grade->feedback }}</p>
                    @endif
                    <p class="mt-2 text-[11px] text-slate-500">Dinilai {{ $grade->graded_at?->diffForHumans() ?? $grade->created_at->diffForHumans() }}</p>
                </article>
            @empty
                <div class="py-12 text-center">
                    <p class="text-sm font-semibold text-slate-800">Belum ada nilai.</p>
                    <p class="mt-1 text-xs text-slate-500">Nilai akan muncul di sini setelah dosen menilai tugas.</p>
                </div>
            @endforelse
        </div>

        {{ $grades->links() }}
    </div>
</x-layout>