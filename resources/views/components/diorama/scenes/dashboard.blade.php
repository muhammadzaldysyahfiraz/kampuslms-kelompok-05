{{-- ====================================================================
     DIORAMA STAGE SCENE: DASHBOARD ("The Morning Academic Routine")
     - Kiri: Karakter kopi terintegrasi langsung di kartu greeting dashboard
     - Kanan: Tanaman kampus dalam pot (opendoodles-plant)
     - Dahan Kanopi Halus di Sudut Atas
     - Garis Tanah Organik Presisi dengan Bayangan Kontak Datar
     ==================================================================== --}}
<div class="diorama-stage-container fixed inset-0 pointer-events-none select-none overflow-hidden z-0" aria-hidden="true">

    {{-- CANOPY DAHAN HALUS ASIMETRIS --}}
    <x-diorama.canopy class="hidden 2xl:block" />

    {{-- HERO KANAN: Tanaman Kampus Potted Flora (Asimetri Organik) --}}
    <div class="hero-diorama hidden 2xl:block absolute z-10 transition-all duration-200"
         style="width: clamp(110px, 8vw, 140px);
                right: max(20px, calc((100vw - 1280px) / 2 - clamp(110px, 8vw, 140px) - 20px));
                bottom: calc(var(--ground, 32px) - 4px);">
        
        {{-- Kontak Bayangan Tanah Datar Tipis --}}
        <div class="absolute left-[10%] right-[10%] bottom-0 h-2 rounded-full pointer-events-none"
             style="background: radial-gradient(ellipse at center, rgba(47, 52, 55, 0.18) 0%, rgba(47, 52, 55, 0) 70%);"></div>

        <div class="hero-art relative z-10 filter drop-shadow-xs">
            <x-diorama.doodle name="plant" size="140" class="w-full h-auto" />
        </div>
    </div>

    {{-- Garis Lantai Studio Bersama (Grounding Baseline) --}}
    <div class="absolute bottom-0 inset-x-0 w-full h-[80px] z-0">
        <svg class="diorama-ground w-full h-full pointer-events-none select-none" viewBox="0 0 1440 80" fill="none" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M-20 48 C240 45 480 49 720 50 C960 51 1200 48 1460 48"
                  style="vector-effect: non-scaling-stroke;"
                  stroke="var(--ink, #2F3437)" stroke-width="1.35" stroke-linecap="round" stroke-linejoin="round" stroke-opacity="0.4" />
            <path d="M120 57 C220 55 320 57 420 56 M1020 57 C1120 55 1220 57 1320 56"
                  style="vector-effect: non-scaling-stroke;"
                  stroke="var(--ink, #2F3437)" stroke-width="1.1" stroke-linecap="round" stroke-linejoin="round" stroke-opacity="0.22" />
        </svg>
    </div>

</div>
