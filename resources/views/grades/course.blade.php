<x-layout title="Rekap Nilai — {{ $course->name }}">
    <div class="mx-auto max-w-7xl space-y-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-slate-500">{{ $course->code }}</p>
                <h1 class="mt-1 text-xl font-bold text-slate-900">Rekap Nilai Kelas: {{ $course->name }}</h1>
                <p class="mt-1 text-xs text-slate-600">{{ $students->count() }} mahasiswa, {{ $assignments->count() }} tugas, {{ $totalGraded }} pengumpulan sudah dinilai.</p>
            </div>
            <p class="text-sm text-slate-700">Rata-rata kelas: <strong>{{ $classAverage ?? '-' }}</strong></p>
        </div>

        <div class="overflow-x-auto border-y border-slate-200">
            <table class="w-full min-w-[760px] text-left text-xs">
                <thead class="border-b border-slate-200 bg-slate-50 text-slate-600">
                    <tr>
                        <th class="sticky left-0 bg-slate-50 px-3 py-3 font-semibold">Mahasiswa</th>
                        @foreach ($assignments as $assignment)
                            <th class="min-w-32 px-3 py-3 font-semibold">{{ $assignment->title }}</th>
                        @endforeach
                        <th class="px-3 py-3 font-semibold">Rata-rata</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($students as $student)
                        <tr>
                            <th class="sticky left-0 bg-white px-3 py-3 font-semibold text-slate-900">{{ $student->name }}</th>
                            @foreach ($assignments as $assignment)
                                @php($submission = $submissionMatrix[$student->id][$assignment->id] ?? null)
                                <td class="px-3 py-3 text-slate-700">
                                    @if ($submission)
                                        @if ($submission->grade)
                                            <a href="{{ route('submissions.show', $submission) }}" class="font-mono font-semibold underline decoration-slate-300 underline-offset-4">{{ $submission->grade->score }}</a>
                                        @else
                                            <a href="{{ route('submissions.show', $submission) }}" class="font-semibold text-amber-800 underline decoration-amber-300 underline-offset-4">Nilai</a>
                                        @endif
                                        @if ($submission->is_late)<span class="ml-1 text-rose-700">Telat</span>@endif
                                    @else
                                        <span class="text-slate-400">Belum kumpul</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="px-3 py-3 font-mono font-semibold text-slate-900">{{ $studentAverages[$student->id] ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ max(2, $assignments->count() + 2) }}" class="px-3 py-10 text-center text-slate-500">Belum ada mahasiswa terdaftar.</td></tr>
                    @endforelse
                </tbody>
                @if ($assignments->isNotEmpty())
                    <tfoot class="border-t border-slate-200 bg-slate-50 font-semibold text-slate-700">
                        <tr>
                            <th class="sticky left-0 bg-slate-50 px-3 py-3 text-left">Rata-rata tugas</th>
                            @foreach ($assignments as $assignment)
                                <td class="px-3 py-3 font-mono">{{ $assignmentAverages[$assignment->id] ?? '-' }}</td>
                            @endforeach
                            <td class="px-3 py-3">-</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</x-layout>