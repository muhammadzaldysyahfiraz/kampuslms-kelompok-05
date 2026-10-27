<x-layout title="{{ $activeRole === 'mahasiswa' ? 'Riwayat Pengumpulan Tugas' : 'Daftar Pengumpulan Tugas' }}">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                @if ($activeRole === 'mahasiswa')
                    Riwayat Pengumpulan Tugas Saya
                @elseif ($activeRole === 'dosen')
                    Antrean & Riwayat Pengumpulan Tugas
                @else
                    Rekap Pengumpulan Tugas Sistem
                @endif
            </h1>
            <p class="text-xs text-slate-600 mt-0.5">
                @if ($activeRole === 'mahasiswa')
                    Daftar berkas jawaban tugas yang telah Anda kumpulkan beserta status penilaiannya.
                @elseif ($activeRole === 'dosen')
                    Seluruh berkas jawaban mahasiswa pada mata kuliah yang Anda ampu.
                @else
                    Pantauan berkas pengumpulan tugas lintas mata kuliah di KampusLMS.
                @endif
            </p>
        </div>

        <a href="{{ route('dashboard') }}" class="btn-outline text-xs py-2 w-fit">
            ← Kembali ke Dashboard
        </a>
    </div>

    <div class="lms-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs" aria-label="Tabel daftar pengumpulan tugas">
                <thead class="bg-slate-50/80 text-[11px] font-semibold text-slate-600 border-b border-slate-200">
                    <tr>
                        <th scope="col" class="px-5 py-3">Tugas & Mata Kuliah</th>
                        @if ($activeRole !== 'mahasiswa')
                            <th scope="col" class="px-5 py-3">Mahasiswa</th>
                        @endif
                        <th scope="col" class="px-5 py-3">Berkas Jawaban</th>
                        <th scope="col" class="px-5 py-3">Waktu Kumpul</th>
                        <th scope="col" class="px-5 py-3">Status & Nilai</th>
                        <th scope="col" class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($submissions as $sub)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-5 py-3.5">
                                <a href="{{ route('assignments.show', $sub->assignment_id) }}" class="font-semibold text-slate-900 hover:underline block">
                                    {{ $sub->assignment->title ?? 'Tugas' }}
                                </a>
                                <span class="text-[11px] font-mono text-slate-500">
                                    {{ $sub->assignment->course->code ?? 'MK' }} · {{ $sub->assignment->course->name ?? '-' }}
                                </span>
                            </td>

                            @if ($activeRole !== 'mahasiswa')
                                <td class="px-5 py-3.5">
                                    <span class="font-semibold text-slate-900 block">{{ $sub->student->name ?? 'Mahasiswa' }}</span>
                                    <span class="text-[11px] font-mono text-slate-500">{{ $sub->student->nim_nip ?? $sub->student->email ?? '-' }}</span>
                                </td>
                            @endif

                            <td class="px-5 py-3.5">
                                <span class="font-medium text-slate-900 block truncate max-w-[200px]">{{ $sub->original_name }}</span>
                                <span class="text-[11px] font-mono text-slate-500">
                                    {{ $sub->file_size ? \Illuminate\Support\Number::fileSize($sub->file_size) : '-' }}
                                </span>
                            </td>

                            <td class="px-5 py-3.5 font-mono text-[11px] text-slate-600">
                                {{ $sub->submitted_at?->format('d M Y, H:i') ?? $sub->created_at->format('d M Y, H:i') }}
                                @if ($sub->is_late)
                                    <span class="block text-[10px] text-rose-700 font-semibold mt-0.5">Terlambat</span>
                                @endif
                            </td>

                            <td class="px-5 py-3.5">
                                @if ($sub->grade)
                                    <span class="status status-ontime">
                                        <span class="status-dot" aria-hidden="true"></span>Nilai: {{ number_format($sub->grade->score, 0) }} / {{ $sub->assignment->max_score ?? 100 }}
                                    </span>
                                @else
                                    <span class="status status-draft">
                                        <span class="status-dot" aria-hidden="true"></span>Menunggu Nilai
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-3.5 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('submissions.download', $sub) }}" class="text-xs font-semibold text-slate-700 hover:text-slate-950 px-2.5 py-1 rounded border border-slate-200 bg-white hover:bg-slate-50 transition-colors">
                                        Unduh
                                    </a>
                                    <a href="{{ route('submissions.show', $sub) }}" class="text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 px-2.5 py-1 rounded transition-colors">
                                        {{ $activeRole === 'dosen' && ! $sub->grade ? 'Nilai' : 'Detail' }}
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $activeRole !== 'mahasiswa' ? 6 : 5 }}" class="px-5 py-8 text-center text-slate-500">
                                Belum ada berkas pengumpulan tugas yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($submissions->hasPages())
        <div class="mt-6 flex justify-center">
            {{ $submissions->links() }}
        </div>
    @endif

</x-layout>
