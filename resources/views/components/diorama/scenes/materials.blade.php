{{-- ====================================================================
     DIORAMA STAGE SCENE: MATERIALS ("Botanical Reading Garden & Archive")
     - Kiri: Mahasiswa fokus mendalam / meditasi belajar (opendoodles-meditating)
     - Kanan: Mahasiswa membaca sambil berbaring santai (opendoodles-laying)
     - Dahan Kanopi Halus di Sudut Atas
     - Garis Tanah Organik Presisi dengan Bayangan Kontak Datar
     ==================================================================== --}}
<div class="diorama-stage-container fixed inset-0 pointer-events-none select-none overflow-hidden z-0" aria-hidden="true">

    {{-- CANOPY DAHAN HALUS ASIMETRIS --}}
    <x-diorama.canopy class="hidden 2xl:block" />

    {{-- HERO KIRI: Mahasiswa Fokus Mendalam / Meditasi Belajar --}}
    <div class="hero-diorama hidden 2xl:block absolute z-10 transition-all duration-200"
         style="width: clamp(150px, 11vw, 195px);
                left: max(16px, calc((100vw - 1280px) / 2 - clamp(150px, 11vw, 195px) - 20px));
                bottom: calc(var(--ground, 32px) - 4px);">
        
        {{-- Kontak Bayangan Tanah Datar Tipis --}}
        <div class="absolute left-[15%] right-[15%] bottom-0 h-2 rounded-full pointer-events-none"
             style="background: radial-gradient(ellipse at center, rgba(47, 52, 55, 0.22) 0%, rgba(47, 52, 55, 0) 70%);"></div>

        <div class="hero-art relative z-10 filter drop-shadow-xs">
            <x-diorama.doodle name="meditating" size="195" class="w-full h-auto" />
        </div>
    </div>

    {{-- HERO KANAN: Mahasiswa Membaca Santai --}}
    <div class="hero-diorama hidden 2xl:block absolute z-10 transition-all duration-200"
         style="width: clamp(170px, 13vw, 225px);
                right: max(20px, calc((100vw - 1280px) / 2 - clamp(170px, 13vw, 225px) - 20px));
                bottom: calc(var(--ground, 32px) - 2px);">
        
        {{-- Kontak Bayangan Tanah Datar Tipis --}}
        <div class="absolute left-[10%] right-[10%] bottom-0 h-2 rounded-full pointer-events-none"
             style="background: radial-gradient(ellipse at center, rgba(47, 52, 55, 0.2) 0%, rgba(47, 52, 55, 0) 70%);"></div>

        <div class="hero-art relative z-10 filter drop-shadow-xs">
            <x-diorama.doodle name="laying" size="225" class="w-full h-auto" />
        </div>
    </div>

    {{-- Garis Lantai Studio Bersama --}}
    <div class="absolute bottom-0 inset-x-0 w-full h-[80px] z-0">
        <svg class="diorama-ground w-full h-full pointer-events-none select-none" viewBox="0 0 1440 80" fill="none" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M-20 48 C240 45 480 49 720 48 C960 50 1200 48 1460 48"
                  style="vector-effect: non-scaling-stroke;"
                  stroke="var(--ink, #2F3437)" stroke-width="1.35" stroke-linecap="round" stroke-linejoin="round" stroke-opacity="0.4" />
            <path d="M90 57 C190 55 290 57 390 56 M1050 57 C1150 55 1250 57 1350 56"
                  style="vector-effect: non-scaling-stroke;"
                  stroke="var(--ink, #2F3437)" stroke-width="1.1" stroke-linecap="round" stroke-linejoin="round" stroke-opacity="0.22" />
        </svg>
    </div>

</div>
