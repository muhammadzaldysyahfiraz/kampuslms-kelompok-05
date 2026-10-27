@props([
    'class' => '',
])

{{-- ====================================================================
     DIORAMA CANOPY: DELICATE BOTANICAL CORNER BRANCHES (Notion Style)
     Proporsi Halus & Anggun:
     - Dikecilkan agar menjadi aksen sudut yang elegan, tidak mendominasi layar.
     - Dahan kiri: h-[110px] xl:h-[135px]
     - Dahan kanan (asimetri 88%): h-[95px] xl:h-[118px]
     - Goresan pensil halus: 1.4px stroke-width
     ==================================================================== --}}
<div class="diorama-canopy absolute top-0 inset-x-0 w-full h-[120px] xl:h-[145px] pointer-events-none select-none overflow-visible z-0 {{ $class }}" aria-hidden="true">

    {{-- 1. DAHAN SUDUT KIRI ATAS (Delicate Botanical Accent) --}}
    <svg class="canopy canopy-left absolute top-0 left-0 h-[110px] xl:h-[135px] w-auto max-w-[40vw]" 
         viewBox="-20 -15 500 200" preserveAspectRatio="xMinYMin meet" fill="none" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <path id="leaf-fall-left" d="M145 145 C136 158 145 170 154 167 C161 163 158 152 145 145 Z" 
                  fill="var(--sage, #8FA895)" fill-opacity="0.32" stroke="var(--ink, #2F3437)" stroke-width="1.0" stroke-linejoin="round"/>
        </defs>
        <g class="branch-left" opacity="var(--canopy-opacity, 0.82)">
            <path d="M-20 -15 C60 20 150 42 270 38 C330 35 390 18 450 -5" 
                  stroke="var(--ink, #2F3437)" stroke-width="var(--branch-stroke, 1.4)" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M125 35 C155 68 185 92 230 102" 
                  stroke="var(--ink, #2F3437)" stroke-width="calc(var(--branch-stroke, 1.4) * 0.85)" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M240 38 C275 62 310 72 355 74" 
                  stroke="var(--ink, #2F3437)" stroke-width="calc(var(--branch-stroke, 1.4) * 0.8)" stroke-linecap="round" stroke-linejoin="round"/>

            <!-- Daun-daun Kiri -->
            <path d="M72 18 C67 38 86 54 100 48 C108 42 100 24 72 18 Z" 
                  fill="var(--sage, #8FA895)" fill-opacity="0.35" stroke="var(--ink, #2F3437)" stroke-width="1.2" stroke-linejoin="round"/>
            <path d="M77 22 C86 35 94 44 98 46" stroke="var(--ink, #2F3437)" stroke-width="0.8" stroke-linecap="round"/>

            <path d="M165 72 C154 98 172 118 186 113 C198 106 195 85 165 72 Z" 
                  fill="var(--sage, #8FA895)" fill-opacity="0.38" stroke="var(--ink, #2F3437)" stroke-width="1.2" stroke-linejoin="round"/>
            <path d="M170 78 C177 93 182 105 185 111" stroke="var(--ink, #2F3437)" stroke-width="0.8" stroke-linecap="round"/>

            <path d="M228 100 C222 120 241 134 252 126 C260 118 253 104 228 100 Z" 
                  fill="var(--sage, #8FA895)" fill-opacity="0.35" stroke="var(--ink, #2F3437)" stroke-width="1.15" stroke-linejoin="round"/>

            <path d="M205 38 C215 14 238 10 248 21 C252 33 234 43 205 38 Z" 
                  fill="var(--sage, #8FA895)" fill-opacity="0.3" stroke="var(--ink, #2F3437)" stroke-width="1.15" stroke-linejoin="round"/>

            <path d="M295 66 C290 87 307 99 318 92 C325 85 320 71 295 66 Z" 
                  fill="var(--sage, #8FA895)" fill-opacity="0.38" stroke="var(--ink, #2F3437)" stroke-width="1.15" stroke-linejoin="round"/>

            <path d="M410 10 C425 -6 448 0 452 14 C452 26 433 26 410 10 Z" 
                  fill="var(--sage, #8FA895)" fill-opacity="0.35" stroke="var(--ink, #2F3437)" stroke-width="1.15" stroke-linejoin="round"/>

            <!-- Ritme Daun Gugur Kiri (3 Daun) -->
            <use href="#leaf-fall-left"/>
            <use href="#leaf-fall-left" transform="translate(110 7) rotate(40 145 145)" opacity="0.8"/>
            <use href="#leaf-fall-left" transform="translate(-85 -57) rotate(-30 145 145)" opacity="0.65"/>
        </g>
    </svg>

    {{-- 2. DAHAN SUDUT KANAN ATAS (Asimetris 88%) --}}
    <svg class="canopy canopy-right absolute top-0 right-0 h-[95px] xl:h-[118px] w-auto max-w-[40vw]" 
         viewBox="960 -15 500 200" preserveAspectRatio="xMaxYMin meet" fill="none" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <path id="leaf-fall-right" d="M1295 145 C1304 158 1295 170 1286 167 C1279 163 1282 152 1295 145 Z" 
                  fill="var(--sage, #8FA895)" fill-opacity="0.32" stroke="var(--ink, #2F3437)" stroke-width="1.0" stroke-linejoin="round"/>
        </defs>
        <g class="branch-right" opacity="var(--canopy-opacity, 0.82)">
            <path d="M1460 -15 C1380 20 1290 42 1170 38 C1110 35 1050 18 990 -5" 
                  stroke="var(--ink, #2F3437)" stroke-width="var(--branch-stroke, 1.4)" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M1315 35 C1285 68 1255 92 1210 102" 
                  stroke="var(--ink, #2F3437)" stroke-width="calc(var(--branch-stroke, 1.4) * 0.85)" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M1200 38 C1165 62 1130 72 1085 74" 
                  stroke="var(--ink, #2F3437)" stroke-width="calc(var(--branch-stroke, 1.4) * 0.8)" stroke-linecap="round" stroke-linejoin="round"/>

            <!-- Daun-daun Kanan -->
            <path d="M1368 18 C1373 38 1354 54 1340 48 C1332 42 1340 24 1368 18 Z" 
                  fill="var(--sage, #8FA895)" fill-opacity="0.35" stroke="var(--ink, #2F3437)" stroke-width="1.2" stroke-linejoin="round"/>
            <path d="M1363 22 C1354 35 1346 44 1342 46" stroke="var(--ink, #2F3437)" stroke-width="0.8" stroke-linecap="round"/>

            <path d="M1275 72 C1286 98 1268 118 1254 113 C1242 106 1245 85 1275 72 Z" 
                  fill="var(--sage, #8FA895)" fill-opacity="0.38" stroke="var(--ink, #2F3437)" stroke-width="1.2" stroke-linejoin="round"/>
            <path d="M1270 78 C1263 93 1258 105 1255 111" stroke="var(--ink, #2F3437)" stroke-width="0.8" stroke-linecap="round"/>

            <path d="M1212 100 C1218 120 1199 134 1188 126 C1180 118 1187 104 1212 100 Z" 
                  fill="var(--sage, #8FA895)" fill-opacity="0.35" stroke="var(--ink, #2F3437)" stroke-width="1.15" stroke-linejoin="round"/>

            <path d="M1235 38 C1225 14 1202 10 1192 21 C1188 33 1206 43 1235 38 Z" 
                  fill="var(--sage, #8FA895)" fill-opacity="0.3" stroke="var(--ink, #2F3437)" stroke-width="1.15" stroke-linejoin="round"/>

            <path d="M1145 66 C1150 87 1133 99 1122 92 C1115 85 1120 71 1145 66 Z" 
                  fill="var(--sage, #8FA895)" fill-opacity="0.38" stroke="var(--ink, #2F3437)" stroke-width="1.15" stroke-linejoin="round"/>

            <path d="M1030 10 C1015 -6 992 0 988 14 C988 26 1007 26 1030 10 Z" 
                  fill="var(--sage, #8FA895)" fill-opacity="0.35" stroke="var(--ink, #2F3437)" stroke-width="1.15" stroke-linejoin="round"/>

            <!-- Ritme Daun Gugur Kanan (2 Daun) -->
            <use href="#leaf-fall-right"/>
            <use href="#leaf-fall-right" transform="translate(-75 -35) rotate(-35 1295 145)" opacity="0.8"/>
        </g>
    </svg>

</div>
