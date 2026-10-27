{{-- ====================================================================
     DIORAMA STAGE SCENE: ERROR PAGES (Ambient Minimalist Ground)
     - Karakter protagonis utama telah di-render langsung di dalam kartu error
       (404: opendoodles-zombieing, 403: opendoodles-clumsy)
     - Latar belakang hanya menampilkan kanopi halus dan garis tanah tenang
     ==================================================================== --}}
<div class="diorama-stage-container fixed inset-0 pointer-events-none select-none overflow-hidden z-0" aria-hidden="true">

    {{-- CANOPY DAHAN HALUS ASIMETRIS --}}
    <x-diorama.canopy class="hidden lg:block" />

    {{-- Garis Lantai Studio Bersama --}}
    <div class="absolute bottom-0 inset-x-0 w-full h-[80px] z-0">
        <svg class="diorama-ground w-full h-full pointer-events-none select-none" viewBox="0 0 1440 80" fill="none" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M-20 48 C240 45 480 49 720 50 C960 49 1200 47 1460 48"
                  style="vector-effect: non-scaling-stroke;"
                  stroke="var(--ink, #2F3437)" stroke-width="1.35" stroke-linecap="round" stroke-linejoin="round" stroke-opacity="0.35" />
            <path d="M120 57 C220 55 320 57 420 56 M1020 57 C1120 55 1220 57 1320 56"
                  style="vector-effect: non-scaling-stroke;"
                  stroke="var(--ink, #2F3437)" stroke-width="1.1" stroke-linecap="round" stroke-linejoin="round" stroke-opacity="0.2" />
        </svg>
    </div>

</div>
