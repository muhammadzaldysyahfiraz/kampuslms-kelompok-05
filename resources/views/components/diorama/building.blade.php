@props([
    'type' => 'gate',
    'class' => '',
])

<div class="diorama-building select-none pointer-events-none {{ $class }}">
    @if ($type === 'gate')
        {{-- Gerbang Kampus Klasik Minimalis dengan Aksen Sage & Biru Redup --}}
        <svg viewBox="0 0 240 180" fill="none" class="w-full h-auto" xmlns="http://www.w3.org/2000/svg"
             stroke="#2F3437" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
            {{-- Pilar Kiri & Kanan --}}
            <rect x="24" y="48" width="22" height="112" fill="#FFFFFF" fill-opacity="0.8" stroke-opacity="0.65" />
            <rect x="194" y="48" width="22" height="112" fill="#FFFFFF" fill-opacity="0.8" stroke-opacity="0.65" />
            <path d="M18 48 H52 M188 48 H222" stroke-opacity="0.7" />
            {{-- Kubah / Atap Busur Gerbang dengan aksen sage --}}
            <path d="M46 58 C85 24 155 24 194 58" stroke-opacity="0.65" />
            <path d="M46 48 C85 14 155 14 194 48" stroke-opacity="0.75" />
            <path d="M85 28 C105 16 135 16 155 28" fill="#8FA895" fill-opacity="0.18" stroke-opacity="0.5" />
            {{-- Kisi-kisi Besi Gerbang --}}
            <path d="M70 70 V156 M100 62 V156 M140 62 V156 M170 70 V156" stroke-opacity="0.35" stroke-dasharray="2 3" />
            <circle cx="120" cy="52" r="8" fill="#94A8B8" fill-opacity="0.2" stroke-opacity="0.6" />
        </svg>
    @elseif ($type === 'library')
        {{-- Paviliun Perpustakaan Akademik --}}
        <svg viewBox="0 0 280 180" fill="none" class="w-full h-auto" xmlns="http://www.w3.org/2000/svg"
             stroke="#2F3437" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 75 L140 32 L260 75 Z" fill="#94A8B8" fill-opacity="0.16" stroke-opacity="0.7" />
            <rect x="36" y="75" width="208" height="90" fill="#FFFFFF" fill-opacity="0.8" stroke-opacity="0.65" />
            <path d="M68 75 V165 M116 75 V165 M164 75 V165 M212 75 V165" stroke-opacity="0.4" />
            <path d="M120 125 C120 108 160 108 160 125 V165 H120 Z" fill="#8FA895" fill-opacity="0.2" stroke-opacity="0.65" />
        </svg>
    @elseif ($type === 'hall')
        {{-- Aula Kampus & Menara Jam --}}
        <svg viewBox="0 0 260 200" fill="none" class="w-full h-auto" xmlns="http://www.w3.org/2000/svg"
             stroke="#2F3437" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
            <rect x="180" y="30" width="56" height="150" fill="#FFFFFF" fill-opacity="0.8" stroke-opacity="0.65" />
            <path d="M174 30 L208 6 L242 30 Z" fill="#8FA895" fill-opacity="0.2" stroke-opacity="0.7" />
            <circle cx="208" cy="55" r="9" stroke-opacity="0.65" />
            <path d="M208 50 V55 H213" stroke-opacity="0.7" />
            <rect x="24" y="85" width="156" height="95" fill="#FFFFFF" fill-opacity="0.8" stroke-opacity="0.65" />
            <path d="M16 85 L102 48 L188 85 Z" fill="#94A8B8" fill-opacity="0.16" stroke-opacity="0.7" />
        </svg>
    @elseif ($type === 'rotunda')
        {{-- Rotunda Akademik & Jembatan --}}
        <svg viewBox="0 0 240 180" fill="none" class="w-full h-auto" xmlns="http://www.w3.org/2000/svg"
             stroke="#2F3437" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
            <ellipse cx="120" cy="50" rx="60" ry="22" fill="#8FA895" fill-opacity="0.16" stroke-opacity="0.65" />
            <path d="M60 50 V140 C60 152 180 152 180 140 V50" fill="#FFFFFF" fill-opacity="0.75" stroke-opacity="0.65" />
            <path d="M84 50 V142 M120 50 V144 M156 50 V142" stroke-opacity="0.4" />
        </svg>
    @endif
</div>
