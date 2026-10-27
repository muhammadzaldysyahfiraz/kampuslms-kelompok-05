@props([
    'variant' => 'auth',
    'class' => '',
])

{{-- Garis kontur tanah organik bersama (Hand-drawn ground contour) --}}
<svg class="diorama-ground w-full pointer-events-none select-none {{ $class }}" viewBox="0 0 1440 120" fill="none" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
    @if ($variant === 'auth')
        {{-- Kontur tanah Stage Auth: Menopang karakter kiri, melengkung di bawah kartu tengah, dan menopang karakter kanan --}}
        <path d="M-20 85 C140 82 280 88 440 92 C560 95 680 96 800 95 C980 93 1180 86 1460 90"
              stroke="#2F3437" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" stroke-opacity="0.55" />
        <path d="M60 96 C110 94 150 96 210 99 M1220 98 C1280 97 1340 99 1400 97"
              stroke="#2F3437" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" stroke-opacity="0.35" />
        {{-- Tekstur rumput / batu kerikil minimalis --}}
        <path d="M110 84 L114 77 M114 84 L118 78 M118 84 L122 79"
              stroke="#2F3437" stroke-width="1.25" stroke-linecap="round" stroke-opacity="0.4" />
        <path d="M320 90 L324 83 M324 90 L328 84"
              stroke="#2F3437" stroke-width="1.25" stroke-linecap="round" stroke-opacity="0.35" />
        <path d="M1310 88 L1314 81 M1314 88 L1318 82 M1318 88 L1322 83"
              stroke="#2F3437" stroke-width="1.25" stroke-linecap="round" stroke-opacity="0.4" />
    @else
        {{-- Kontur tanah Ambient Strip --}}
        <path d="M-10 75 C240 70 480 80 720 75 C960 70 1200 80 1450 75"
              stroke="#2F3437" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" stroke-opacity="0.5" />
        <path d="M80 84 C180 82 260 85 360 84 M1080 84 C1180 82 1260 85 1360 84"
              stroke="#2F3437" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" stroke-opacity="0.3" />
    @endif
</svg>
