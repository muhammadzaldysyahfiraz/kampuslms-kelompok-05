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
        } elseif (request()->routeIs('login', 'password.*')) {
            $scene = 'auth';
        } else {
            $scene = 'error';
        }
    }
@endphp

{{-- ====================================================================
     NOTION-INSPIRED ARCHITECTURAL CAMPUS SKETCH (EKSKLUSIF OPENDOODLES)
     Karakteristik Visual Terpadu:
     - Latar belakang putih gading hangat (#FAF8F5)
     - Eksklusif menggunakan koleksi ilustrasi CC0 Open Doodles (Pablo Stanley)
     - Palet Warna Tinta Terpadu (--bg-page, --ink, --illus-ink, --sage, --blue)
     - Garis tanah studio presisi dengan bayangan kontak datar organik
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
        background-color: rgba(255, 255, 255, 0.98) !important;
        backdrop-filter: blur(8px);
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
</style>

<div id="notion-campus-backdrop" data-scene="{{ $scene }}" aria-hidden="true">
    @if ($scene === 'auth')
        <x-diorama.scenes.auth />
    @elseif ($scene === 'dashboard')
        <x-diorama.scenes.dashboard />
    @elseif ($scene === 'courses')
        <x-diorama.scenes.courses />
    @elseif ($scene === 'assignments')
        <x-diorama.scenes.assignments />
    @elseif ($scene === 'materials')
        <x-diorama.scenes.materials />
    @elseif ($scene === 'users')
        <x-diorama.scenes.users />
    @elseif ($scene === 'tentang')
        <x-diorama.scenes.tentang />
    @else
        <x-diorama.scenes.error />
    @endif
</div>
