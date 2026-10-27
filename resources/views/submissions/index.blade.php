<x-layout title="Daftar Pengumpulan">
    <div class="mx-auto max-w-6xl space-y-5">
        <div>
            <p class="text-xs font-semibold uppercase text-slate-500">Tugas dan penilaian</p>
            <h1 class="mt-1 text-xl font-bold text-slate-900">Daftar Pengumpulan</h1>
        </div>

        <div class="overflow-x-auto border-y border-slate-200">
            <table class="w-full min-w-[720px] text-left text-sm">
                <thead class="border-b border-slate-200 text-xs text-slate-500">
                    <tr>
                        <th class="py-3 pr-4 font-semibold">Mahasiswa</th>
                        <th class="py-3 pr-4 font-semibold">Tugas</th>
                        <th class="py-3 pr-4 font-semibold">Dikumpulkan</th>
                        <th class="py-3 pr-4 font-semibold">Status</th>
                        <th class="py-3 pr-4 font-semibold">Nilai</th>
                        <th class="py-3 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($submissions as $submission)
                        <tr>
                            <td class="py-3 pr-4 font-semibold text-slate-900">{{ $submission->student->name }}</td>
                            <td class="py-3 pr-4 text-slate-700">{{ $submission->assignment->title }}</td>
                            <td class="py-3 pr-4 text-xs text-slate-600">{{ $submission->submitted_at?->format('d M Y, H:i') ?? '-' }}</td>
                            <td class="py-3 pr-4 text-xs {{ $submission->is_late ? 'text-rose-700' : 'text-emerald-800' }}">{{ $submission->is_late ? 'Terlambat' : 'Tepat waktu' }}</td>
                            <td class="py-3 pr-4 font-mono text-slate-800">{{ $submission->grade?->score ?? 'Belum dinilai' }}</td>
                            <td class="py-3 text-right">
                                <a href="{{ route('submissions.show', $submission) }}" class="text-xs font-semibold text-slate-700 underline decoration-slate-300 underline-offset-4">Buka</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-12 text-center text-sm text-slate-500">Belum ada pengumpulan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $submissions->links() }}
    </div>
</x-layout>