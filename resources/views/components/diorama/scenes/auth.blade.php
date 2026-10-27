{{-- ====================================================================
     DIORAMA STAGE SCENE: AUTH ("The Notion Reading Lounge - Harmonized Proportions")
     - Dua Protagonis Utama dengan Ukuran Proporsional:
       1. Kiri: Mahasiswa duduk di kursi membaca (Width: clamp(200px, 17vw, 265px))
       2. Kanan: Mahasiswa duduk di lantai membaca (Width: clamp(190px, 16vw, 250px))
     - Dahan Kanopi Minimalis (Halus & Anggun, tidak mendominasi layar)
     - Kontak Bayangan Tanah Datar Presisi
     ==================================================================== --}}
<div class="diorama-stage-container fixed inset-0 pointer-events-none select-none overflow-hidden z-0" aria-hidden="true">

    {{-- CANOPY DAHAN HALUS ASIMETRIS --}}
    <x-diorama.canopy class="hidden lg:block" />

    {{-- 1. HERO KIRI: Mahasiswa di Kursi + Tumpukan Buku (Card-Anchored & Pixel Grounded) --}}
    <div class="hero-diorama hero-left hidden lg:block absolute z-10 transition-all duration-200"
         style="width: var(--hero-left-w, clamp(200px, 17vw, 265px));
                right: calc(50% + var(--card-half, 224px) + var(--hero-gap-left, 46px));
                bottom: calc(var(--ground, 32px) - (var(--hero-left-w, clamp(200px, 17vw, 265px)) * 0.75 * 0.043));">
        
        {{-- Kontak Bayangan Tanah Datar Tipis di Kaki & Kursi --}}
        <div class="hero-ground-shadow absolute left-[12%] right-[12%] bottom-[calc((var(--hero-left-w,clamp(200px,17vw,265px))*0.75*0.043)-4px)] h-2 rounded-full pointer-events-none"
             style="background: radial-gradient(ellipse at center, rgba(47, 52, 55, 0.22) 0%, rgba(47, 52, 55, 0) 70%);"></div>

        <div data-anime="auth-hero" class="hero-art relative z-10 filter drop-shadow-xs transition-transform duration-300 hover:scale-[1.02]">
            <x-diorama.doodle name="sitting-reading" size="265" class="w-full h-auto" />
        </div>
    </div>

    {{-- 2. HERO KANAN: Mahasiswa Duduk di Lantai (Card-Anchored & Pixel Grounded) --}}
    <div class="hero-diorama hero-right hidden lg:block absolute z-10 transition-all duration-200"
         style="width: var(--hero-right-w, calc(clamp(200px, 17vw, 265px) * 0.95));
                left: calc(50% + var(--card-half, 224px) + var(--hero-gap-right, 60px));
                bottom: calc(var(--ground, 32px) - (calc(clamp(200px, 17vw, 265px) * 0.95) * 0.75 * 0.12));">
        
        {{-- Kontak Bayangan Tanah Datar Tipis di Kaki & Tanaman --}}
        <div class="hero-ground-shadow absolute left-[12%] right-[12%] bottom-[calc((calc(clamp(200px,17vw,265px)*0.95)*0.75*0.12)-4px)] h-2 rounded-full pointer-events-none"
             style="background: radial-gradient(ellipse at center, rgba(47, 52, 55, 0.2) 0%, rgba(47, 52, 55, 0) 70%);"></div>

        <div data-anime="auth-hero" class="hero-art relative z-10 filter drop-shadow-xs transition-transform duration-300 hover:scale-[1.02]">
            <x-diorama.doodle name="reading-side" size="250" flip="true" class="w-full h-auto" />
        </div>
    </div>

    {{-- 3. Garis Lantai Studio Bersama (Grounding Baseline 32px) --}}
    <div class="absolute bottom-0 inset-x-0 w-full h-[80px] z-0">
        <svg class="diorama-ground w-full h-full pointer-events-none select-none" viewBox="0 0 1440 80" fill="none" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M-20 48 C220 45 420 49 600 50 C720 51 840 51 960 49 C1140 46 1320 48 1460 48"
                  style="vector-effect: non-scaling-stroke;"
                  stroke="var(--ink, #2F3437)" stroke-width="1.35" stroke-linecap="round" stroke-linejoin="round" stroke-opacity="0.45" />
            
            <path d="M80 57 C160 55 240 57 320 56 M1120 57 C1200 55 1280 57 1360 56"
                  style="vector-effect: non-scaling-stroke;"
                  stroke="var(--ink, #2F3437)" stroke-width="1.1" stroke-linecap="round" stroke-linejoin="round" stroke-opacity="0.25" />
            
            <circle cx="210" cy="54" r="1.2" fill="var(--ink, #2F3437)" fill-opacity="0.3" />
            <circle cx="216" cy="53" r="0.9" fill="var(--ink, #2F3437)" fill-opacity="0.25" />
            <circle cx="1230" cy="54" r="1.2" fill="var(--ink, #2F3437)" fill-opacity="0.3" />
            <circle cx="1237" cy="53" r="0.9" fill="var(--ink, #2F3437)" fill-opacity="0.25" />
        </svg>
    </div>

</div>
