{{-- Pagination KampusLMS: label Indonesia, kontrol hairline, target sentuh 44px, umpan balik 8-state --}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

        {{-- Ringkasan data: satu sumber informasi posisi halaman --}}
        <p class="text-xs text-slate-600">
            Menampilkan
            @if ($paginator->firstItem())
                <span class="font-semibold text-slate-900 font-mono">{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</span>
            @else
                <span class="font-semibold text-slate-900 font-mono">{{ $paginator->count() }}</span>
            @endif
            dari <span class="font-semibold text-slate-900 font-mono">{{ $paginator->total() }}</span> data
        </p>

        <div class="flex items-center gap-1.5">

            {{-- Halaman sebelumnya --}}
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" aria-label="Halaman sebelumnya"
                      class="inline-flex items-center justify-center gap-1.5 h-11 px-3 rounded-lg border border-slate-200 bg-slate-50 text-xs font-semibold text-slate-400 cursor-not-allowed">
                    <svg class="w-3.5 h-3.5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                    <span class="hidden sm:inline">Sebelumnya</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Halaman sebelumnya"
                   class="inline-flex items-center justify-center gap-1.5 h-11 px-3 rounded-lg border border-slate-200 bg-white text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-50 hover:border-slate-300 active:scale-[0.97]">
                    <svg class="w-3.5 h-3.5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                    <span class="hidden sm:inline">Sebelumnya</span>
                </a>
            @endif

            {{-- Nomor halaman (disembunyikan di layar sempit agar tidak terjadi overflow horizontal) --}}
            <div class="hidden sm:flex items-center gap-1.5">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="inline-flex items-center justify-center h-11 w-9 text-xs font-semibold text-slate-400 cursor-default" aria-hidden="true">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page"
                                      class="inline-flex items-center justify-center h-11 w-11 rounded-lg border border-slate-900 bg-slate-900 text-xs font-semibold text-white font-mono">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}" aria-label="Ke halaman {{ $page }}"
                                   class="inline-flex items-center justify-center h-11 w-11 rounded-lg border border-slate-200 bg-white text-xs font-semibold text-slate-700 font-mono transition-colors hover:bg-slate-50 hover:border-slate-300 active:scale-[0.97]">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            {{-- Halaman berikutnya --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Halaman berikutnya"
                   class="inline-flex items-center justify-center gap-1.5 h-11 px-3 rounded-lg border border-slate-200 bg-white text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-50 hover:border-slate-300 active:scale-[0.97]">
                    <span class="hidden sm:inline">Berikutnya</span>
                    <svg class="w-3.5 h-3.5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </a>
            @else
                <span aria-disabled="true" aria-label="Halaman berikutnya"
                      class="inline-flex items-center justify-center gap-1.5 h-11 px-3 rounded-lg border border-slate-200 bg-slate-50 text-xs font-semibold text-slate-400 cursor-not-allowed">
                    <span class="hidden sm:inline">Berikutnya</span>
                    <svg class="w-3.5 h-3.5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </span>
            @endif

        </div>
    </nav>
@endif
