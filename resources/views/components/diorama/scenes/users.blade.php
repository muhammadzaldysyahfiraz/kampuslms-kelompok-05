{{-- ====================================================================
     DIORAMA STAGE SCENE: USERS ("Campus Community Boulevard")
     - Kiri: Mahasiswa berswafoto bersama profil civitas (opendoodles-selfie)
     - Kanan: Mahasiswa berinteraksi hangat dengan kucing kampus (opendoodles-petting)
     - Dahan Kanopi Halus di Sudut Atas
     - Garis Tanah Organik Presisi dengan Bayangan Kontak Datar
     ==================================================================== --}}
<div class="diorama-stage-container fixed inset-0 pointer-events-none select-none overflow-hidden z-0" aria-hidden="true">

    {{-- CANOPY DAHAN HALUS ASIMETRIS --}}
    <x-diorama.canopy class="hidden 2xl:block" />

    {{-- HERO KIRI: Mahasiswa Berpose Profil Kampus --}}
    <div class="hero-diorama hidden 2xl:block absolute z-10 transition-all duration-200"
         style="width: clamp(140px, 11vw, 185px);
                left: max(16px, calc((100vw - 1280px) / 2 - clamp(140px, 11vw, 185px) - 20px));
                bottom: calc(var(--ground, 32px) - 4px);">
        
        {{-- Kontak Bayangan Tanah Datar Tipis --}}
        <div class="absolute left-[15%] right-[15%] bottom-0 h-2 rounded-full pointer-events-none"
             style="background: radial-gradient(ellipse at center, rgba(47, 52, 55, 0.22) 0%, rgba(47, 52, 55, 0) 70%);"></div>

        <div class="hero-art relative z-10 filter drop-shadow-xs">
            <x-diorama.doodle name="selfie" size="185" class="w-full h-auto" />
        </div>
    </div>

    {{-- HERO KANAN: Mahasiswa Mengelus Kucing Kampus --}}
    <div class="hero-diorama hidden 2xl:block absolute z-10 transition-all duration-200"
         style="width: clamp(150px, 11vw, 195px);
                right: max(20px, calc((100vw - 1280px) / 2 - clamp(150px, 11vw, 195px) - 20px));
                bottom: calc(var(--ground, 32px) - 4px);">
        
        {{-- Kontak Bayangan Tanah Datar Tipis --}}
        <div class="absolute left-[12%] right-[12%] bottom-0 h-2 rounded-full pointer-events-none"
             style="background: radial-gradient(ellipse at center, rgba(47, 52, 55, 0.2) 0%, rgba(47, 52, 55, 0) 70%);"></div>

        <div class="hero-art relative z-10 filter drop-shadow-xs">
            <x-diorama.doodle name="petting" size="195" class="w-full h-auto" />
        </div>
    </div>

    {{-- Garis Lantai Studio Bersama --}}
    <div class="absolute bottom-0 inset-x-0 w-full h-[80px] z-0">
        <svg class="diorama-ground w-full h-full pointer-events-none select-none" viewBox="0 0 1440 80" fill="none" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M-20 48 C240 47 480 49 720 50 C960 48 1200 49 1460 48"
                  style="vector-effect: non-scaling-stroke;"
                  stroke="var(--ink, #2F3437)" stroke-width="1.35" stroke-linecap="round" stroke-linejoin="round" stroke-opacity="0.4" />
            <path d="M110 57 C210 55 310 57 410 56 M1030 57 C1130 55 1230 57 1330 56"
                  style="vector-effect: non-scaling-stroke;"
                  stroke="var(--ink, #2F3437)" stroke-width="1.1" stroke-linecap="round" stroke-linejoin="round" stroke-opacity="0.22" />
        </svg>
    </div>

</div>
