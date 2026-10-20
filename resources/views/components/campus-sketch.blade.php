@props(['scene' => null])

@php
    // Tentukan variasi komposisi ilustrasi berdasarkan rute halaman aktif
    if (! $scene) {
        if (request()->routeIs('dashboard')) {
            $scene = 'dashboard';
        } elseif (request()->routeIs('courses.*')) {
            $scene = 'courses';
        } elseif (request()->routeIs('assignments.*', 'dosen.assignments.*', 'submissions.*', 'mahasiswa.submissions.*')) {
            $scene = 'assignments';
        } elseif (request()->routeIs('materials.*', 'dosen.materials.*')) {
            $scene = 'materials';
        } elseif (request()->routeIs('users.*')) {
            $scene = 'users';
        } elseif (request()->routeIs('tentang')) {
            $scene = 'tentang';
        } else {
            $scene = 'auth';
        }
    }
@endphp

{{-- ====================================================================
     NOTION-INSPIRED ARCHITECTURAL CAMPUS SKETCH (EKSKLUSIF OPENDOODLES)
     Karakteristik Visual:
     - Latar belakang putih hangat / gading (#FAF8F5)
     - Eksklusif menggunakan 12 ilustrasi CC0 dari OpenDoodles (Pablo Stanley)
       yang dinormalisasi ke palet arang (#475569), hijau sage (#8FA895),
       dan biru redup (#94A8B8), dipadukan dengan sketsa garis luar arsitektur
       kampus minimalis yang seragam (1.35px, round cap & round join).
     ==================================================================== --}}
<style>
    html,
    body {
        background-color: #FAF8F5 !important;
    }

    /*
     * ISOLASI STACKING CONTEXT (ANTI-NEMBUS):
     * #notion-campus-backdrop berada di z-index: 0 sebagai anak pertama <body>,
     * sedangkan seluruh elemen konten saudara di <body> (header, main, footer, dsb.)
     * berada di z-index >= 10 dengan permukaan kartu #FFFFFF yang solid.
     */
    #notion-campus-backdrop {
        position: fixed;
        inset: 0;
        pointer-events: none;
        z-index: 0;
        overflow: hidden;
        user-select: none;
    }

    body > *:not(#notion-campus-backdrop):not(script):not(style) {
        position: relative;
        z-index: 10;
    }

    header[role="banner"] {
        position: sticky !important;
        top: 0;
        z-index: 30 !important;
        background-color: rgba(255, 255, 255, 0.96) !important;
        backdrop-filter: blur(6px);
    }

    footer[role="contentinfo"] {
        position: relative;
        z-index: 20 !important;
        background-color: #ffffff !important;
    }

    .lms-card,
    .two-tone-card {
        background-color: #ffffff !important;
    }

    /* Penyelarasan kontras lembut untuk ilustrasi OpenDoodles di latar belakang */
    .campus-asset-doodle {
        opacity: 0.45;
        filter: contrast(0.95);
    }
</style>

<div id="notion-campus-backdrop" data-scene="{{ $scene }}" aria-hidden="true">

    @if ($scene === 'dashboard')
        {{-- ================================================================
             SCENE 1: DASHBOARD ("Grand Campus Panorama")
             OpenDoodles: plant.svg (kiri) & reading.svg (kanan)
             Arsitektur: Paviliun Perpustakaan Kiri, Gedung Aula & Menara Jam Kanan
             ================================================================ --}}
        <div class="fixed bottom-0 left-0 w-[330px] sm:w-[440px] xl:w-[520px] h-[340px]">
            <img src="{{ asset('images/illustrations/opendoodles-plant.svg') }}"
                 alt=""
                 class="campus-asset-doodle absolute bottom-[66px] left-[8px] w-[88px] sm:w-[104px] h-auto" />

            <svg class="absolute inset-0 w-full h-full" viewBox="0 0 520 340" fill="none" xmlns="http://www.w3.org/2000/svg"
                 stroke="#475569" stroke-width="1.35" stroke-linecap="round" stroke-linejoin="round">
                {{-- Aksen blok warna lembut (Sage Green & Muted Blue) --}}
                <path d="M312 248 C296 248 286 234 288 218 C286 202 298 190 312 192 C326 190 338 202 336 218 C338 234 328 248 312 248 Z"
                      fill="#8FA895" fill-opacity="0.16" stroke="none" />
                <path d="M106 184 L190 142 L274 184 Z" fill="#94A8B8" fill-opacity="0.16" stroke="none" />
                <rect x="120" y="184" width="140" height="86" fill="#FFFFFF" fill-opacity="0.72" stroke="none" />
                <path d="M176 270 C170 292 140 314 90 336 L162 336 C192 314 204 292 204 270 Z"
                      fill="#E7E2DA" fill-opacity="0.52" stroke="none" />

                {{-- Horizon & Jalan Setapak --}}
                <path d="M-10 270 C75 268 180 270 290 272 C365 273 430 278 495 286" stroke-opacity="0.55" />
                <path d="M-10 304 C50 302 115 305 175 312" stroke-opacity="0.28" />
                <path d="M176 270 C170 292 140 314 90 336" stroke-opacity="0.52" />
                <path d="M204 270 C204 292 192 314 162 336" stroke-opacity="0.52" />
                <path d="M174 284 H192 M160 300 H182 M140 316 H166" stroke-opacity="0.34" />

                {{-- Paviliun Perpustakaan Minimalis --}}
                <path d="M104 184 L190 140 L276 184" stroke-opacity="0.72" />
                <path d="M112 184 H268" stroke-opacity="0.7" />
                <circle cx="190" cy="166" r="7" fill="#FAF8F5" stroke-opacity="0.68" />
                <path d="M190 162 V166 L193 168" stroke-opacity="0.68" />
                <path d="M120 184 V270 M260 184 V270" stroke-opacity="0.68" />
                <path d="M144 194 V270 M168 194 V270 M212 194 V270 M236 194 V270" stroke-opacity="0.44" />
                <path d="M116 194 H264" stroke-opacity="0.5" />
                <path d="M178 270 V236 C178 228 202 228 202 236 V270" fill="#94A8B8" fill-opacity="0.14" stroke-opacity="0.68" />
                <path d="M190 229 V270" stroke-opacity="0.44" />
                <rect x="127" y="208" width="11" height="22" rx="1.5" stroke-opacity="0.48" />
                <rect x="242" y="208" width="11" height="22" rx="1.5" stroke-opacity="0.48" />

                {{-- Pohon, Bangku Taman & Lampu Jalan --}}
                <path d="M310 272 V222" stroke-opacity="0.62" />
                <path d="M310 236 L301 226 M310 230 L319 221" stroke-opacity="0.52" />
                <path d="M310 246 C294 246 284 232 286 216 C284 200 296 188 310 190 C324 188 336 200 334 216 C336 232 326 246 310 246 Z"
                      stroke-opacity="0.58" />
                <path d="M272 265 H298 M275 260 H295 M276 265 V272 M294 265 V272" stroke-opacity="0.52" />
                <path d="M348 273 V226 M343 273 H353" stroke-opacity="0.52" />
                <circle cx="348" cy="222" r="4" fill="#FAF8F5" stroke-opacity="0.62" />

                {{-- Detail Buku Akademik di Sudut Kiri --}}
                <g transform="translate(18, 286)">
                    <rect x="0" y="16" width="54" height="9" rx="2" fill="#94A8B8" fill-opacity="0.18" stroke-opacity="0.62" />
                    <path d="M5 20.5 H48" stroke-opacity="0.38" />
                    <rect x="6" y="7" width="44" height="9" rx="2" fill="#8FA895" fill-opacity="0.18" stroke-opacity="0.62" />
                </g>
            </svg>
        </div>

        <div class="fixed bottom-0 right-0 w-[330px] sm:w-[440px] xl:w-[520px] h-[340px]">
            <img src="{{ asset('images/illustrations/opendoodles-reading.svg') }}"
                 alt=""
                 class="campus-asset-doodle absolute bottom-[12px] right-[16px] w-[108px] sm:w-[128px] h-auto" />

            <svg class="absolute inset-0 w-full h-full" viewBox="0 0 520 340" fill="none" xmlns="http://www.w3.org/2000/svg"
                 stroke="#475569" stroke-width="1.35" stroke-linecap="round" stroke-linejoin="round">
                {{-- Gedung Aula & Menara Jam Kampus --}}
                <path d="M312 146 C312 124 348 124 348 146 Z" fill="#94A8B8" fill-opacity="0.2" stroke="none" />
                <rect x="246" y="196" width="128" height="76" fill="#FFFFFF" fill-opacity="0.72" stroke="none" />
                <path d="M238 196 L310 168 L382 196 Z" fill="#94A8B8" fill-opacity="0.14" stroke="none" />
                <path d="M432 238 C412 238 400 220 404 198 C400 180 414 164 432 166 C450 164 464 180 462 198 C466 220 452 238 432 238 Z"
                      fill="#8FA895" fill-opacity="0.16" stroke="none" />
                <path d="M196 252 C182 252 172 240 174 226 C172 212 182 202 196 204 C208 202 218 212 216 226 C218 240 208 252 196 252 Z"
                      fill="#8FA895" fill-opacity="0.14" stroke="none" />

                <path d="M25 286 C95 278 165 273 245 272 C345 270 435 270 530 272" stroke-opacity="0.55" />
                <path d="M312 196 V146 H348 V196" fill="#FAF8F5" stroke-opacity="0.65" />
                <path d="M312 146 C312 122 348 122 348 146" stroke-opacity="0.68" />
                <path d="M330 122 V110" stroke-opacity="0.62" />
                <circle cx="330" cy="164" r="6.5" stroke-opacity="0.62" />
                <path d="M330 161 V164 L332.5 166" stroke-opacity="0.62" />

                <path d="M238 196 L310 168 L382 196" stroke-opacity="0.68" />
                <path d="M246 196 V272 M374 196 V272" stroke-opacity="0.68" />
                <path d="M242 196 H378" stroke-opacity="0.58" />
                <path d="M262 234 V214 C262 209 274 209 274 214 V234 Z" stroke-opacity="0.48" />
                <path d="M288 234 V214 C288 209 300 209 300 214 V234 Z" stroke-opacity="0.48" />
                <path d="M320 234 V214 C320 209 332 209 332 214 V234 Z" stroke-opacity="0.48" />
                <path d="M346 234 V214 C346 209 358 209 358 214 V234 Z" stroke-opacity="0.48" />
                <path d="M296 272 C300 294 324 316 362 336" stroke-opacity="0.48" />
                <path d="M322 272 C328 294 354 316 398 336" stroke-opacity="0.48" />

                <path d="M430 272 V210" stroke-opacity="0.65" />
                <path d="M430 236 C410 236 398 218 402 196 C398 178 412 162 430 164 C448 162 462 178 460 196 C464 218 450 236 430 236 Z"
                      stroke-opacity="0.6" />
                <path d="M194 272 V230" stroke-opacity="0.55" />
                <path d="M194 250 C180 250 170 238 172 224 C170 210 180 200 194 202 C206 200 216 210 214 224 C216 238 206 250 194 250 Z"
                      stroke-opacity="0.52" />
            </svg>
        </div>

        <svg class="hidden xl:block fixed top-16 right-6 w-52 h-auto opacity-60"
             viewBox="0 0 220 140" fill="none" xmlns="http://www.w3.org/2000/svg"
             stroke="#475569" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round">
            <path d="M210 12 C175 28 148 54 126 88" stroke-opacity="0.45" />
            <path d="M178 28 C164 20 150 24 148 38 C162 42 172 38 178 28 Z" fill="#8FA895" fill-opacity="0.18" stroke-opacity="0.5" />
            <path d="M158 46 C170 46 180 54 176 66 C164 66 156 58 158 46 Z" fill="#8FA895" fill-opacity="0.15" stroke-opacity="0.5" />
            <path d="M142 64 C130 58 118 62 118 74 C130 76 138 72 142 64 Z" fill="#94A8B8" fill-opacity="0.16" stroke-opacity="0.45" />
        </svg>

    @elseif ($scene === 'courses')
        {{-- ================================================================
             SCENE 2: COURSES ("Academic Courtyard & Observatory Tower")
             OpenDoodles: sitting-reading.svg (kiri bawah), reading-side.svg & plant.svg (kanan)
             Arsitektur: Pelataran Arkade Fakultas di Kiri, Menara Observatorium di Kanan
             ================================================================ --}}
        <div class="fixed bottom-0 left-0 w-[330px] sm:w-[450px] xl:w-[530px] h-[340px]">
            <img src="{{ asset('images/illustrations/opendoodles-sitting-reading.svg') }}"
                 alt=""
                 class="campus-asset-doodle absolute bottom-[12px] left-[16px] w-[104px] sm:w-[124px] h-auto" />

            <svg class="absolute inset-0 w-full h-full" viewBox="0 0 530 340" fill="none" xmlns="http://www.w3.org/2000/svg"
                 stroke="#475569" stroke-width="1.35" stroke-linecap="round" stroke-linejoin="round">
                {{-- Gedung Arkade Fakultas di Sisi Kiri --}}
                <path d="M138 192 L224 156 L310 192 Z" fill="#94A8B8" fill-opacity="0.16" stroke="none" />
                <rect x="148" y="192" width="152" height="78" fill="#FFFFFF" fill-opacity="0.72" stroke="none" />
                <path d="M342 242 C324 242 312 226 316 206 C312 188 326 174 342 176 C358 174 372 188 370 206 C374 226 360 242 342 242 Z"
                      fill="#8FA895" fill-opacity="0.16" stroke="none" />

                <path d="M138 192 L224 156 L310 192" stroke-opacity="0.68" />
                <path d="M148 192 V270 M300 192 V270 M142 192 H306" stroke-opacity="0.65" />
                <path d="M168 270 V234 C168 224 190 224 190 234 V270" fill="#94A8B8" fill-opacity="0.13" stroke-opacity="0.52" />
                <path d="M212 270 V234 C212 224 236 224 236 234 V270" fill="#94A8B8" fill-opacity="0.13" stroke-opacity="0.52" />
                <path d="M258 270 V234 C258 224 280 224 280 234 V270" fill="#94A8B8" fill-opacity="0.13" stroke-opacity="0.52" />

                <path d="M342 271 V212" stroke-opacity="0.6" />
                <path d="M342 240 C324 240 312 224 316 204 C312 186 326 172 342 174 C358 172 372 186 370 204 C374 224 360 240 342 240 Z"
                      stroke-opacity="0.55" />
                <path d="M-10 270 C95 268 210 270 330 274 C400 276 460 282 520 290" stroke-opacity="0.55" />
                <path d="M210 270 C216 294 242 316 280 336" stroke-opacity="0.48" />
                <path d="M238 270 C248 292 278 314 322 336" stroke-opacity="0.48" />
            </svg>
        </div>

        <div class="fixed bottom-0 right-0 w-[330px] sm:w-[440px] xl:w-[520px] h-[340px]">
            <img src="{{ asset('images/illustrations/opendoodles-plant.svg') }}"
                 alt=""
                 class="campus-asset-doodle absolute bottom-[66px] right-[16px] w-[88px] sm:w-[104px] h-auto" />
            <img src="{{ asset('images/illustrations/opendoodles-reading-side.svg') }}"
                 alt=""
                 class="campus-asset-doodle absolute bottom-[12px] right-[96px] w-[96px] sm:w-[114px] h-auto" />

            <svg class="absolute inset-0 w-full h-full" viewBox="0 0 520 340" fill="none" xmlns="http://www.w3.org/2000/svg"
                 stroke="#475569" stroke-width="1.35" stroke-linecap="round" stroke-linejoin="round">
                {{-- Kubah Observatorium Biru Redup --}}
                <path d="M276 168 C276 126 348 126 348 168 Z" fill="#94A8B8" fill-opacity="0.18" stroke-opacity="0.65" />
                <rect x="268" y="168" width="88" height="104" fill="#FFFFFF" fill-opacity="0.72" stroke-opacity="0.65" />
                <path d="M312 126 V108 M296 168 C296 136 328 136 328 168" stroke-opacity="0.5" />
                <circle cx="312" cy="192" r="9" fill="#FAF8F5" stroke-opacity="0.65" />
                <path d="M312 187 V192 L316 195" stroke-opacity="0.65" />
                <path d="M286 242 V216 C286 210 300 210 300 216 V242 Z" fill="#94A8B8" fill-opacity="0.12" stroke-opacity="0.5" />
                <path d="M324 242 V216 C324 210 338 210 338 216 V242 Z" fill="#94A8B8" fill-opacity="0.12" stroke-opacity="0.5" />
                <path d="M218 242 C198 242 186 224 190 202 C186 184 200 168 218 170 C236 168 250 184 248 202 C252 224 238 242 218 242 Z"
                      fill="#8FA895" fill-opacity="0.16" stroke-opacity="0.55" />
                <path d="M218 272 V212" stroke-opacity="0.6" />
                <path d="M15 288 C105 278 200 272 310 272 C400 272 465 272 530 274" stroke-opacity="0.55" />
            </svg>
        </div>

        <svg class="hidden xl:block fixed top-16 left-6 w-52 h-auto opacity-60"
             viewBox="0 0 220 140" fill="none" xmlns="http://www.w3.org/2000/svg"
             stroke="#475569" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10 14 C45 30 72 56 94 90" stroke-opacity="0.45" />
            <path d="M42 30 C56 22 70 26 72 40 C58 44 48 40 42 30 Z" fill="#8FA895" fill-opacity="0.18" stroke-opacity="0.5" />
            <path d="M62 48 C50 48 40 56 44 68 C56 68 64 60 62 48 Z" fill="#94A8B8" fill-opacity="0.16" stroke-opacity="0.5" />
        </svg>

    @elseif ($scene === 'assignments')
        {{-- ================================================================
             SCENE 3: ASSIGNMENTS & SUBMISSIONS ("Study Focus & East Library")
             OpenDoodles: coffee.svg (kiri), unboxing.svg & sitting.svg (kanan)
             Arsitektur: Paviliun Studi Kiri, Perpustakaan Timur Kanan
             ================================================================ --}}
        <div class="fixed bottom-0 left-0 w-[330px] sm:w-[440px] xl:w-[520px] h-[340px]">
            <img src="{{ asset('images/illustrations/opendoodles-coffee.svg') }}"
                 alt=""
                 class="campus-asset-doodle absolute bottom-[64px] left-[18px] w-[96px] sm:w-[114px] h-auto" />

            <svg class="absolute inset-0 w-full h-full" viewBox="0 0 520 340" fill="none" xmlns="http://www.w3.org/2000/svg"
                 stroke="#475569" stroke-width="1.35" stroke-linecap="round" stroke-linejoin="round">
                <path d="M168 188 L236 154 L304 188 Z" fill="#94A8B8" fill-opacity="0.16" stroke-opacity="0.65" />
                <rect x="180" y="188" width="112" height="82" fill="#FFFFFF" fill-opacity="0.72" stroke-opacity="0.65" />
                <path d="M202 188 V270 M224 188 V270 M248 188 V270 M270 188 V270" stroke-opacity="0.42" />
                <path d="M332 178 C322 196 320 222 332 244 C344 222 342 196 332 178 Z"
                      fill="#8FA895" fill-opacity="0.16" stroke-opacity="0.55" />
                <path d="M332 271 V228" stroke-opacity="0.6" />
                <path d="M-10 270 C85 268 195 270 310 272 C385 274 440 280 500 288" stroke-opacity="0.55" />
                <path d="M358 265 H386 M362 260 H382 M362 265 V272 M382 265 V272" stroke-opacity="0.5" />
                {{-- Tumpukan Buku Minimalis --}}
                <g transform="translate(118, 284)">
                    <rect x="0" y="14" width="52" height="8" rx="2" fill="#94A8B8" fill-opacity="0.18" stroke-opacity="0.6" />
                    <rect x="5" y="6" width="42" height="8" rx="2" fill="#8FA895" fill-opacity="0.18" stroke-opacity="0.6" />
                </g>
            </svg>
        </div>

        <div class="fixed bottom-0 right-0 w-[330px] sm:w-[440px] xl:w-[520px] h-[340px]">
            <img src="{{ asset('images/illustrations/opendoodles-unboxing.svg') }}"
                 alt=""
                 class="campus-asset-doodle absolute bottom-[68px] right-[18px] w-[96px] sm:w-[114px] h-auto" />
            <img src="{{ asset('images/illustrations/opendoodles-sitting.svg') }}"
                 alt=""
                 class="campus-asset-doodle absolute bottom-[10px] right-[136px] w-[86px] sm:w-[102px] h-auto" />

            <svg class="absolute inset-0 w-full h-full" viewBox="0 0 520 340" fill="none" xmlns="http://www.w3.org/2000/svg"
                 stroke="#475569" stroke-width="1.35" stroke-linecap="round" stroke-linejoin="round">
                {{-- Gedung Perpustakaan Sayap Kanan --}}
                <path d="M214 186 L292 146 L370 186 Z" fill="#94A8B8" fill-opacity="0.16" stroke="none" />
                <rect x="226" y="186" width="132" height="86" fill="#FFFFFF" fill-opacity="0.72" stroke="none" />
                <path d="M214 186 L292 146 L370 186" stroke-opacity="0.68" />
                <path d="M226 186 V272 M358 186 V272 M220 186 H364" stroke-opacity="0.65" />
                <path d="M280 272 V238 C280 230 304 230 304 238 V272" fill="#94A8B8" fill-opacity="0.14" stroke-opacity="0.62" />
                <path d="M20 288 C100 278 180 273 260 272 C355 270 440 270 530 272" stroke-opacity="0.55" />
            </svg>
        </div>

        <svg class="hidden xl:block fixed top-16 right-8 w-48 h-auto opacity-60"
             viewBox="0 0 200 120" fill="none" xmlns="http://www.w3.org/2000/svg"
             stroke="#475569" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round">
            <g transform="translate(110, 18)">
                <path d="M28 6 C18 3 8 3 0 6 V26 C8 23 18 23 28 26 C38 23 48 23 56 26 V6 C48 3 38 3 28 6 Z"
                      fill="#FFFFFF" fill-opacity="0.85" stroke-opacity="0.6" />
                <path d="M28 6 V26 M6 11 H22 M34 11 H50" stroke-opacity="0.4" />
            </g>
            <path d="M190 54 C162 64 138 78 118 102" stroke-opacity="0.42" />
            <path d="M156 66 C144 60 132 64 132 76 C144 78 152 74 156 66 Z" fill="#8FA895" fill-opacity="0.18" stroke-opacity="0.48" />
        </svg>

    @elseif ($scene === 'materials')
        {{-- ================================================================
             SCENE 4: MATERIALS ("Botanical Reading Garden & Archive")
             OpenDoodles: plant.svg & laying.svg (kiri), reading-side.svg (kanan)
             Arsitektur: Pergola Taman Baca Kiri, Rak Pustaka & Perpustakaan Kanan
             ================================================================ --}}
        <div class="fixed bottom-0 left-0 w-[330px] sm:w-[440px] xl:w-[520px] h-[340px]">
            <img src="{{ asset('images/illustrations/opendoodles-plant.svg') }}"
                 alt=""
                 class="campus-asset-doodle absolute bottom-[68px] left-[14px] w-[90px] sm:w-[106px] h-auto" />
            <img src="{{ asset('images/illustrations/opendoodles-laying.svg') }}"
                 alt=""
                 class="campus-asset-doodle absolute bottom-[14px] left-[42px] w-[115px] sm:w-[138px] h-auto" />

            <svg class="absolute inset-0 w-full h-full" viewBox="0 0 520 340" fill="none" xmlns="http://www.w3.org/2000/svg"
                 stroke="#475569" stroke-width="1.35" stroke-linecap="round" stroke-linejoin="round">
                <path d="M216 271 V202 M296 271 V202 M206 202 H306 M210 196 H302" stroke-opacity="0.55" />
                <path d="M234 271 V222 C234 210 278 210 278 222 V271" fill="#8FA895" fill-opacity="0.14" stroke-opacity="0.52" />
                <path d="M342 244 C324 244 312 228 316 208 C312 190 326 176 342 178 C358 176 372 190 370 208 C374 228 360 244 342 244 Z"
                      fill="#8FA895" fill-opacity="0.16" stroke-opacity="0.55" />
                <path d="M342 271 V216" stroke-opacity="0.6" />
                <path d="M-10 270 C80 268 185 270 300 272 C375 274 435 279 495 286" stroke-opacity="0.55" />
            </svg>
        </div>

        <div class="fixed bottom-0 right-0 w-[330px] sm:w-[440px] xl:w-[520px] h-[340px]">
            <img src="{{ asset('images/illustrations/opendoodles-reading-side.svg') }}"
                 alt=""
                 class="campus-asset-doodle absolute bottom-[14px] right-[20px] w-[104px] sm:w-[122px] h-auto" />

            <svg class="absolute inset-0 w-full h-full" viewBox="0 0 520 340" fill="none" xmlns="http://www.w3.org/2000/svg"
                 stroke="#475569" stroke-width="1.35" stroke-linecap="round" stroke-linejoin="round">
                <path d="M232 184 L312 144 L392 184 Z" fill="#94A8B8" fill-opacity="0.16" stroke="none" />
                <rect x="244" y="184" width="136" height="88" fill="#FFFFFF" fill-opacity="0.72" stroke="none" />
                <path d="M232 184 L312 144 L392 184" stroke-opacity="0.68" />
                <path d="M244 184 V272 M380 184 V272 M238 184 H386" stroke-opacity="0.65" />
                <path d="M268 194 V272 M296 194 V272 M328 194 V272 M356 194 V272" stroke-opacity="0.42" />
                <path d="M25 286 C100 278 175 273 255 272 C350 270 435 270 530 272" stroke-opacity="0.55" />
                <path d="M168 265 H196 M172 260 H192 M172 265 V272 M192 265 V272" stroke-opacity="0.5" />
            </svg>
        </div>

    @elseif ($scene === 'users')
        {{-- ================================================================
             SCENE 5: USERS ("Campus Community Boulevard")
             OpenDoodles: strolling.svg (kiri), chilling.svg & sleek.svg (kanan)
             Arsitektur: Boulevard Kampus, Lampu Taman & Gedung Rektorat
             ================================================================ --}}
        <div class="fixed bottom-0 left-0 w-[330px] sm:w-[440px] xl:w-[520px] h-[340px]">
            <img src="{{ asset('images/illustrations/opendoodles-strolling.svg') }}"
                 alt=""
                 class="campus-asset-doodle absolute bottom-[52px] left-[28px] w-[96px] sm:w-[116px] h-auto" />

            <svg class="absolute inset-0 w-full h-full" viewBox="0 0 520 340" fill="none" xmlns="http://www.w3.org/2000/svg"
                 stroke="#475569" stroke-width="1.35" stroke-linecap="round" stroke-linejoin="round">
                <path d="M186 242 C166 242 154 224 158 202 C154 184 168 168 186 170 C204 168 218 184 216 202 C220 224 206 242 186 242 Z"
                      fill="#8FA895" fill-opacity="0.16" stroke-opacity="0.55" />
                <path d="M186 270 V210" stroke-opacity="0.6" />
                <path d="M286 246 C268 246 256 230 260 210 C256 192 270 178 286 180 C302 178 316 192 314 210 C318 230 304 246 286 246 Z"
                      fill="#8FA895" fill-opacity="0.15" stroke-opacity="0.55" />
                <path d="M286 271 V216" stroke-opacity="0.6" />
                <path d="M228 265 H258 M232 260 H254 M232 265 V271 M254 265 V271" stroke-opacity="0.52" />
                <path d="M332 272 V220 M326 272 H338" stroke-opacity="0.55" />
                <circle cx="332" cy="215" r="4.5" fill="#FAF8F5" stroke-opacity="0.65" />
                <path d="M-10 270 C85 268 190 270 305 272 C380 274 440 279 500 286" stroke-opacity="0.55" />
                <path d="M72 270 C82 294 112 316 156 336" stroke-opacity="0.45" />
                <path d="M108 270 C122 294 156 316 206 336" stroke-opacity="0.45" />
            </svg>
        </div>

        <div class="fixed bottom-0 right-0 w-[330px] sm:w-[440px] xl:w-[520px] h-[340px]">
            <img src="{{ asset('images/illustrations/opendoodles-chilling.svg') }}"
                 alt=""
                 class="campus-asset-doodle absolute bottom-[12px] right-[18px] w-[102px] sm:w-[120px] h-auto" />
            <img src="{{ asset('images/illustrations/opendoodles-sleek.svg') }}"
                 alt=""
                 class="campus-asset-doodle absolute bottom-[66px] right-[145px] w-[78px] sm:w-[92px] h-auto" />

            <svg class="absolute inset-0 w-full h-full" viewBox="0 0 520 340" fill="none" xmlns="http://www.w3.org/2000/svg"
                 stroke="#475569" stroke-width="1.35" stroke-linecap="round" stroke-linejoin="round">
                <path d="M256 192 L328 162 L400 192 Z" fill="#94A8B8" fill-opacity="0.16" stroke-opacity="0.65" />
                <rect x="266" y="192" width="124" height="80" fill="#FFFFFF" fill-opacity="0.72" stroke-opacity="0.65" />
                <path d="M25 286 C100 278 175 273 255 272 C350 270 435 270 530 272" stroke-opacity="0.55" />
                <path d="M300 272 C306 294 330 316 368 336" stroke-opacity="0.48" />
                <path d="M328 272 C336 294 362 316 404 336" stroke-opacity="0.48" />
            </svg>
        </div>

    @elseif ($scene === 'tentang')
        {{-- ================================================================
             SCENE 6: TENTANG KELOMPOK ("Collaborative Rotunda & Campus Bridge")
             OpenDoodles: meditating.svg & plant.svg (kiri), coffee.svg & strolling.svg (kanan)
             Arsitektur: Jembatan Taman Kampus & Rotunda Akademik
             ================================================================ --}}
        <div class="fixed bottom-0 left-0 w-[330px] sm:w-[440px] xl:w-[520px] h-[340px]">
            <img src="{{ asset('images/illustrations/opendoodles-plant.svg') }}"
                 alt=""
                 class="campus-asset-doodle absolute bottom-[66px] left-[16px] w-[86px] sm:w-[102px] h-auto" />
            <img src="{{ asset('images/illustrations/opendoodles-meditating.svg') }}"
                 alt=""
                 class="campus-asset-doodle absolute bottom-[12px] left-[155px] w-[96px] sm:w-[114px] h-auto" />

            <svg class="absolute inset-0 w-full h-full" viewBox="0 0 520 340" fill="none" xmlns="http://www.w3.org/2000/svg"
                 stroke="#475569" stroke-width="1.35" stroke-linecap="round" stroke-linejoin="round">
                <path d="M304 272 C328 248 368 248 392 272" fill="#94A8B8" fill-opacity="0.14" stroke-opacity="0.55" />
                <path d="M300 260 C328 238 368 238 396 260" stroke-opacity="0.5" />
                <path d="M320 249 V262 M338 244 V257 M358 244 V257 M376 249 V262" stroke-opacity="0.4" />
                <path d="M-10 270 C85 268 190 270 304 272 M392 272 C435 274 475 279 510 285" stroke-opacity="0.55" />
            </svg>
        </div>

        <div class="fixed bottom-0 right-0 w-[330px] sm:w-[440px] xl:w-[520px] h-[340px]">
            <img src="{{ asset('images/illustrations/opendoodles-coffee.svg') }}"
                 alt=""
                 class="campus-asset-doodle absolute bottom-[66px] right-[20px] w-[90px] sm:w-[106px] h-auto" />
            <img src="{{ asset('images/illustrations/opendoodles-strolling.svg') }}"
                 alt=""
                 class="campus-asset-doodle absolute bottom-[12px] right-[145px] w-[88px] sm:w-[104px] h-auto" />

            <svg class="absolute inset-0 w-full h-full" viewBox="0 0 520 340" fill="none" xmlns="http://www.w3.org/2000/svg"
                 stroke="#475569" stroke-width="1.35" stroke-linecap="round" stroke-linejoin="round">
                <path d="M252 178 C252 142 324 142 324 178 Z" fill="#94A8B8" fill-opacity="0.18" stroke-opacity="0.65" />
                <rect x="242" y="178" width="92" height="94" fill="#FFFFFF" fill-opacity="0.72" stroke-opacity="0.65" />
                <path d="M262 178 V272 M314 178 V272" stroke-opacity="0.45" />
                <path d="M25 286 C100 278 175 273 255 272 C350 270 435 270 530 272" stroke-opacity="0.55" />
            </svg>
        </div>

    @else
        {{-- ================================================================
             SCENE 7: AUTH & ERRORS ("Campus Gateway & Welcoming Courtyard")
             OpenDoodles: plant.svg & sitting-reading.svg (kiri), reading.svg (kanan)
             ================================================================ --}}
        <div class="fixed bottom-0 left-0 w-[320px] sm:w-[420px] xl:w-[500px] h-[340px]">
            <img src="{{ asset('images/illustrations/opendoodles-plant.svg') }}"
                 alt=""
                 class="campus-asset-doodle absolute bottom-[64px] left-[12px] w-[84px] sm:w-[98px] h-auto" />
            <img src="{{ asset('images/illustrations/opendoodles-sitting-reading.svg') }}"
                 alt=""
                 class="campus-asset-doodle absolute bottom-[12px] left-[96px] w-[94px] sm:w-[110px] h-auto" />

            <svg class="absolute inset-0 w-full h-full" viewBox="0 0 500 340" fill="none" xmlns="http://www.w3.org/2000/svg"
                 stroke="#475569" stroke-width="1.35" stroke-linecap="round" stroke-linejoin="round">
                <path d="M184 188 L256 150 L328 188 Z" fill="#94A8B8" fill-opacity="0.16" stroke-opacity="0.65" />
                <rect x="196" y="188" width="120" height="84" fill="#FFFFFF" fill-opacity="0.72" stroke-opacity="0.65" />
                <path d="M-10 272 C85 270 190 272 305 274 C375 276 435 280 490 286" stroke-opacity="0.55" />
                <path d="M242 272 C236 294 210 316 168 336" stroke-opacity="0.48" />
                <path d="M270 272 C270 294 256 316 226 336" stroke-opacity="0.48" />
            </svg>
        </div>

        <div class="fixed bottom-0 right-0 w-[320px] sm:w-[420px] xl:w-[500px] h-[340px]">
            <img src="{{ asset('images/illustrations/opendoodles-reading.svg') }}"
                 alt=""
                 class="campus-asset-doodle absolute bottom-[12px] right-[18px] w-[100px] sm:w-[118px] h-auto" />

            <svg class="absolute inset-0 w-full h-full" viewBox="0 0 500 340" fill="none" xmlns="http://www.w3.org/2000/svg"
                 stroke="#475569" stroke-width="1.35" stroke-linecap="round" stroke-linejoin="round">
                <path d="M292 152 C292 130 328 130 328 152 Z" fill="#94A8B8" fill-opacity="0.2" stroke-opacity="0.65" />
                <rect x="284" y="152" width="52" height="120" fill="#FFFFFF" fill-opacity="0.72" stroke-opacity="0.65" />
                <circle cx="310" cy="176" r="6.5" stroke-opacity="0.62" />
                <path d="M226 244 C208 244 196 228 200 208 C196 190 210 176 226 178 C242 176 256 190 254 208 C258 228 244 244 226 244 Z"
                      fill="#8FA895" fill-opacity="0.16" stroke-opacity="0.55" />
                <path d="M226 272 V216" stroke-opacity="0.6" />
                <path d="M15 286 C90 278 165 274 245 272 C340 270 425 270 510 272" stroke-opacity="0.55" />
            </svg>
        </div>
    @endif

</div>
