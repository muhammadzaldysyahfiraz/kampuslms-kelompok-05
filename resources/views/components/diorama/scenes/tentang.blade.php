{{-- ====================================================================
     DIORAMA STAGE SCENE: TENTANG ("Collaborative Rotunda & Team Spirit")
     - Kiri: Mahasiswa menari merayakan pencapaian (opendoodles-dancing)
     - Kanan: Karakter loving terintegrasi langsung di banner tim tentang.blade.php
     - Dahan Kanopi Halus di Sudut Atas
     - Garis Tanah Organik Presisi dengan Bayangan Kontak Datar
     ==================================================================== --}}
<div class="diorama-stage-container fixed inset-0 pointer-events-none select-none overflow-hidden z-0" aria-hidden="true">

    {{-- CANOPY DAHAN HALUS ASIMETRIS --}}
    <x-diorama.canopy class="hidden 2xl:block" />

    {{-- HERO KIRI: Mahasiswa Merayakan Kolaborasi Tim --}}
    <div class="hero-diorama hidden 2xl:block absolute z-10 transition-all duration-200"
         style="width: clamp(140px, 11vw, 185px);
                left: max(16px, calc((100vw - 1280px) / 2 - clamp(140px, 11vw, 185px) - 20px));
                bottom: calc(var(--ground, 32px) - 4px);">
        
        {{-- Kontak Bayangan Tanah Datar Tipis --}}
        <div class="absolute left-[15%] right-[15%] bottom-0 h-2 rounded-full pointer-events-none"
             style="background: radial-gradient(ellipse at center, rgba(47, 52, 55, 0.22) 0%, rgba(47, 52, 55, 0) 70%);"></div>

        <div class="hero-art relative z-10 filter drop-shadow-xs">
            <x-diorama.doodle name="dancing" size="185" class="w-full h-auto" />
        </div>
    </div>

    {{-- Garis Lantai Studio Bersama --}}
    <div class="absolute bottom-0 inset-x-0 w-full h-[80px] z-0">
        <svg class="diorama-ground w-full h-full pointer-events-none select-none" viewBox="0 0 1440 80" fill="none" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M-20 48 C240 46 480 50 720 48 C960 50 1200 48 1460 48"
                  style="vector-effect: non-scaling-stroke;"
                  stroke="var(--ink, #2F3437)" stroke-width="1.35" stroke-linecap="round" stroke-linejoin="round" stroke-opacity="0.4" />
            <path d="M100 57 C200 55 300 57 400 56 M1040 57 C1140 55 1240 57 1340 56"
                  style="vector-effect: non-scaling-stroke;"
                  stroke="var(--ink, #2F3437)" stroke-width="1.1" stroke-linecap="round" stroke-linejoin="round" stroke-opacity="0.22" />
        </svg>
    </div>

</div>
