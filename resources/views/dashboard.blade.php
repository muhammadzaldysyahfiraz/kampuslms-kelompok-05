<x-layout title="Dashboard">

    {{-- ====================================================================
         DASHBOARD 3-PANEL LAYOUT (Sesuai image_013.jpg & DESIGN.md Section 5.1)
         Middle Area (Main Content) + Right Panel (Profile, Streak, Schedule)
         ==================================================================== --}}
    <div class="flex flex-col xl:flex-row gap-8 items-start">
        
        {{-- ================================================================
             AREA TENGAH: Dashboard Main Content
             ================================================================ --}}
        <div class="flex-1 min-w-0 w-full space-y-7">
            
            {{-- Header: Judul & Search Bar --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Dashboard
                </h1>

                {{-- Search Bar dengan Icon Kaca Pembesar & Mic --}}
                <div class="relative w-full sm:w-96">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        placeholder="What do you want to learn today?" 
                        class="w-full pl-10 pr-10 py-2.5 bg-white rounded-xl border border-slate-200 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#66A7F2] shadow-xs"
                    >
                    <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 cursor-pointer hover:text-slate-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 0 0 6-6v-1.5m-6 7.5a6 6 0 0 1-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 0 1-3-3V4.5a3 3 0 1 1 6 0v8.25a3 3 0 0 1-3 3Z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- SECTION: My courses --}}
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-extrabold text-slate-900 tracking-tight">My courses</h2>
                    <a href="{{ route('courses.index') }}" class="btn-outline text-xs py-1.5 px-3">
                        See all
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    
                    {{-- Course Card 1: Cinema 4D (Amber Accent) --}}
                    <div class="lms-card p-5 relative overflow-hidden flex flex-col justify-between hover:shadow-md transition-shadow">
                        <div>
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <h3 class="font-extrabold text-base text-slate-900">Cinema 4D</h3>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Started 04.01.2022</p>
                                </div>
                                <div class="text-[#FFC152] cursor-pointer hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                        <path d="M5 4a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v18l-7-3.5L5 22V4z"/>
                                    </svg>
                                </div>
                            </div>

                            {{-- Row Avatars Peserta + Chevron --}}
                            <div class="flex items-center justify-between my-4">
                                <div class="flex -space-x-2 overflow-hidden">
                                    <img class="inline-block h-7 w-7 rounded-full ring-2 ring-white" src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=64&h=64&fit=crop&crop=faces" alt="Avatar">
                                    <img class="inline-block h-7 w-7 rounded-full ring-2 ring-white" src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=64&h=64&fit=crop&crop=faces" alt="Avatar">
                                    <img class="inline-block h-7 w-7 rounded-full ring-2 ring-white" src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=64&h=64&fit=crop&crop=faces" alt="Avatar">
                                </div>
                                <span class="w-6 h-6 rounded-full bg-[#1E1E26] text-white flex items-center justify-center text-[10px] cursor-pointer">
                                    ▼
                                </span>
                            </div>
                        </div>

                        {{-- Progress bar & Module info --}}
                        <div class="pt-3 border-t border-slate-100">
                            <div class="flex items-center justify-between text-[11px] font-bold text-slate-500 mb-1.5">
                                <span>07/10 modules</span>
                                <span class="text-slate-900">70%</span>
                            </div>
                            <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-[#FFC152] rounded-full" style="width: 70%"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Course Card 2: Front-End (Mint Accent) --}}
                    <div class="lms-card p-5 relative overflow-hidden flex flex-col justify-between hover:shadow-md transition-shadow">
                        <div>
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <h3 class="font-extrabold text-base text-slate-900">Front-End</h3>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Started 14.12.2021</p>
                                </div>
                                <div class="text-[#5DD299] cursor-pointer hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                        <path d="M5 4a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v18l-7-3.5L5 22V4z"/>
                                    </svg>
                                </div>
                            </div>

                            {{-- Row Avatars Peserta + Chevron --}}
                            <div class="flex items-center justify-between my-4">
                                <div class="flex -space-x-2 overflow-hidden">
                                    <img class="inline-block h-7 w-7 rounded-full ring-2 ring-white" src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=64&h=64&fit=crop&crop=faces" alt="Avatar">
                                    <img class="inline-block h-7 w-7 rounded-full ring-2 ring-white" src="https://images.unsplash.com/photo-1492562080023-ab3db95bfbce?w=64&h=64&fit=crop&crop=faces" alt="Avatar">
                                </div>
                                <span class="w-6 h-6 rounded-full bg-[#1E1E26] text-white flex items-center justify-center text-[10px] cursor-pointer">
                                    ▼
                                </span>
                            </div>
                        </div>

                        {{-- Progress bar & Module info --}}
                        <div class="pt-3 border-t border-slate-100">
                            <div class="flex items-center justify-between text-[11px] font-bold text-slate-500 mb-1.5">
                                <span>02/10 modules</span>
                                <span class="text-slate-900">20%</span>
                            </div>
                            <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-[#5DD299] rounded-full" style="width: 20%"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Course Card 3: Graphic design (Coral Accent) --}}
                    <div class="lms-card p-5 relative overflow-hidden flex flex-col justify-between hover:shadow-md transition-shadow">
                        <div>
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <h3 class="font-extrabold text-base text-slate-900">Graphic design</h3>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Started 14.12.2021</p>
                                </div>
                                <div class="text-[#FE774C] cursor-pointer hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                        <path d="M5 4a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v18l-7-3.5L5 22V4z"/>
                                    </svg>
                                </div>
                            </div>

                            {{-- Row Avatars Peserta + Chevron --}}
                            <div class="flex items-center justify-between my-4">
                                <div class="flex -space-x-2 overflow-hidden">
                                    <img class="inline-block h-7 w-7 rounded-full ring-2 ring-white" src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=64&h=64&fit=crop&crop=faces" alt="Avatar">
                                    <img class="inline-block h-7 w-7 rounded-full ring-2 ring-white" src="https://images.unsplash.com/photo-1517841905240-472988babdf9?w=64&h=64&fit=crop&crop=faces" alt="Avatar">
                                </div>
                                <span class="w-6 h-6 rounded-full bg-[#1E1E26] text-white flex items-center justify-center text-[10px] cursor-pointer">
                                    ▼
                                </span>
                            </div>
                        </div>

                        {{-- Progress bar & Module info --}}
                        <div class="pt-3 border-t border-slate-100">
                            <div class="flex items-center justify-between text-[11px] font-bold text-slate-500 mb-1.5">
                                <span>01/10 modules</span>
                                <span class="text-slate-900">10%</span>
                            </div>
                            <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-[#FE774C] rounded-full" style="width: 10%"></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- SECTION: Baris Tengah (Rating & My scores side by side) --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                {{-- WIDGET 1: Rating (Leaderboard) --}}
                <div class="lms-card p-5 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <h2 class="text-base font-extrabold text-slate-900">Rating</h2>
                                <span class="text-xs text-slate-500 bg-slate-100 px-2 py-0.5 rounded font-semibold cursor-pointer">
                                    Cinema 4D ▾
                                </span>
                            </div>
                            <button class="btn-outline text-xs py-1 px-2.5">See all</button>
                        </div>

                        {{-- Stats Baris Rating: Place, Score, Homeworks --}}
                        <div class="flex items-center justify-around py-3 px-4 rounded-xl bg-slate-50 border border-slate-100 mb-4">
                            <div class="text-center">
                                <div class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">
                                    <span>14</span>
                                    <span>↑</span>
                                </div>
                                <p class="text-[10px] text-slate-400 mt-1 font-semibold uppercase">Place</p>
                            </div>

                            <div class="text-center">
                                <div class="w-10 h-10 rounded-full bg-[#66A7F2] text-white flex items-center justify-center font-extrabold text-xs shadow-xs mx-auto">
                                    200
                                </div>
                                <p class="text-[10px] text-slate-400 mt-1 font-semibold uppercase">Score</p>
                            </div>

                            <div class="text-center">
                                <span class="text-sm font-extrabold text-slate-800">02/07</span>
                                <p class="text-[10px] text-slate-400 mt-1 font-semibold uppercase">Homeworks</p>
                            </div>
                        </div>

                        {{-- Mini Leaderboard List --}}
                        <div class="space-y-1.5 text-xs">
                            <div class="flex justify-between text-[11px] font-bold text-slate-400 px-2 pb-1">
                                <span>Student</span>
                                <span>HW/Score</span>
                            </div>

                            <div class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 transition-colors">
                                <div class="flex items-center gap-2.5">
                                    <span class="font-mono text-slate-400 font-bold w-4">12</span>
                                    <img class="w-6 h-6 rounded-full" src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=64&h=64&fit=crop&crop=faces" alt="">
                                    <span class="font-bold text-slate-800">Olga Skorokhod</span>
                                </div>
                                <span class="font-mono text-slate-600 font-semibold">4 / 360</span>
                            </div>

                            <div class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 transition-colors">
                                <div class="flex items-center gap-2.5">
                                    <span class="font-mono text-slate-400 font-bold w-4">13</span>
                                    <img class="w-6 h-6 rounded-full" src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=64&h=64&fit=crop&crop=faces" alt="">
                                    <span class="font-bold text-slate-800">Semen Melnik</span>
                                </div>
                                <span class="font-mono text-slate-600 font-semibold">3 / 299</span>
                            </div>

                            {{-- Highlighted Active User (Yuliia Kirieleva) --}}
                            <div class="flex items-center justify-between p-2 rounded-lg bg-[#66A7F2]/10 border border-[#66A7F2]/30 font-bold">
                                <div class="flex items-center gap-2.5">
                                    <span class="font-mono text-[#1d4ed8] font-bold w-4">14</span>
                                    <img class="w-6 h-6 rounded-full ring-1 ring-[#66A7F2]" src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=64&h=64&fit=crop&crop=faces" alt="">
                                    <span class="text-slate-900">Yuliia Kirieleva (You)</span>
                                </div>
                                <span class="font-mono text-[#1d4ed8]">2 / 200</span>
                            </div>

                            <div class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 transition-colors">
                                <div class="flex items-center gap-2.5">
                                    <span class="font-mono text-slate-400 font-bold w-4">15</span>
                                    <img class="w-6 h-6 rounded-full" src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=64&h=64&fit=crop&crop=faces" alt="">
                                    <span class="font-bold text-slate-800">Tom Hidden</span>
                                </div>
                                <span class="font-mono text-slate-600 font-semibold">2 / 190</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- WIDGET 2: My scores (Smooth Curved Area Graph) --}}
                <div class="lms-card p-5 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <h2 class="text-base font-extrabold text-slate-900">My scores</h2>
                            <span class="text-xs text-slate-500 bg-slate-100 px-2 py-0.5 rounded font-semibold cursor-pointer">
                                Cinema 4D ▾
                            </span>
                        </div>

                        {{-- Area Chart SVG (Sesuai grafik kuning di image_013.jpg) --}}
                        <div class="relative mt-4">
                            
                            {{-- Tooltip Data Point Popup --}}
                            <div class="absolute left-24 top-2 bg-white px-2.5 py-1.5 rounded-lg shadow-md border border-slate-200 text-[10px] z-10">
                                <p class="text-slate-500">First pass: <strong class="text-slate-900 font-bold">60</strong></p>
                                <p class="text-slate-500">Retake: <strong class="text-[#FE774C] font-bold">81</strong></p>
                            </div>

                            {{-- SVG Chart --}}
                            <svg class="w-full h-44 overflow-visible" viewBox="0 0 350 140" fill="none">
                                <defs>
                                    <linearGradient id="scoreAmberGradient" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stop-color="#FFC152" stop-opacity="0.85"/>
                                        <stop offset="100%" stop-color="#FFC152" stop-opacity="0.1"/>
                                    </linearGradient>
                                </defs>

                                {{-- Grid horizontal lines --}}
                                <line x1="25" y1="20" x2="340" y2="20" stroke="#F1F5F9" stroke-width="1"/>
                                <line x1="25" y1="50" x2="340" y2="50" stroke="#F1F5F9" stroke-width="1"/>
                                <line x1="25" y1="80" x2="340" y2="80" stroke="#F1F5F9" stroke-width="1"/>
                                <line x1="25" y1="110" x2="340" y2="110" stroke="#F1F5F9" stroke-width="1"/>

                                {{-- Y Axis Labels --}}
                                <text x="5" y="24" class="text-[9px] fill-slate-400 font-mono">100</text>
                                <text x="5" y="54" class="text-[9px] fill-slate-400 font-mono">60</text>
                                <text x="5" y="84" class="text-[9px] fill-slate-400 font-mono">40</text>
                                <text x="5" y="114" class="text-[9px] fill-slate-400 font-mono">20</text>

                                {{-- Area Path Curve --}}
                                <path 
                                    d="M 30 110 Q 70 100 110 55 T 180 50 T 250 65 T 330 50 L 330 120 L 30 120 Z" 
                                    fill="url(#scoreAmberGradient)"
                                />
                                
                                {{-- Stroke Curve --}}
                                <path 
                                    d="M 30 110 Q 70 100 110 55 T 180 50 T 250 65 T 330 50" 
                                    stroke="#FFC152" 
                                    stroke-width="2.5" 
                                    stroke-linecap="round"
                                />

                                {{-- Vertical Dashed Guide line for tooltip --}}
                                <line x1="110" y1="50" x2="110" y2="120" stroke="#94A3B8" stroke-width="1" stroke-dasharray="3 3"/>
                                <circle cx="110" cy="55" r="4" fill="#ffffff" stroke="#FFC152" stroke-width="3"/>
                            </svg>

                            {{-- X Axis Labels --}}
                            <div class="flex justify-between pl-6 pr-2 text-[10px] font-mono text-slate-400 mt-1">
                                <span>HW1</span>
                                <span>HW2</span>
                                <span>HW3</span>
                                <span>HW4</span>
                                <span>HW5</span>
                                <span>HW6</span>
                                <span>HW7</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- SECTION: Homeworks (Accordion List Items) --}}
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Homeworks</h2>
                    <button class="btn-outline text-xs py-1.5 px-3">See all</button>
                </div>

                <div class="space-y-3">
                    
                    {{-- Homework Item 1 --}}
                    <div class="lms-card p-4 flex items-center justify-between hover:border-slate-300 transition-colors cursor-pointer">
                        <div class="flex items-center gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-[#F1D2F1]/50 border border-[#F1D2F1] text-[#701a75] flex items-center justify-center font-extrabold text-sm">
                                ✦
                            </div>
                            <div>
                                <h3 class="text-xs sm:text-sm font-extrabold text-slate-900">
                                    8. Design a brochure for a restaurant
                                </h3>
                                <div class="flex items-center gap-2 text-[11px] text-slate-400 mt-0.5">
                                    <span>Graphic design</span>
                                    <span>•</span>
                                    <span class="flex items-center gap-1">
                                        <span>Level:</span>
                                        <span class="inline-flex gap-0.5">
                                            <span class="w-3 h-1.5 rounded-full bg-[#5DD299]"></span>
                                            <span class="w-3 h-1.5 rounded-full bg-[#5DD299]"></span>
                                            <span class="w-3 h-1.5 rounded-full bg-slate-200"></span>
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <span class="text-slate-400 hover:text-slate-600">
                            ▼
                        </span>
                    </div>

                    {{-- Homework Item 2 --}}
                    <div class="lms-card p-4 flex items-center justify-between hover:border-slate-300 transition-colors cursor-pointer">
                        <div class="flex items-center gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-amber-50 border border-[#FFC152]/40 text-[#925400] flex items-center justify-center font-extrabold text-xs font-mono">
                                4D
                            </div>
                            <div>
                                <h3 class="text-xs sm:text-sm font-extrabold text-slate-900">
                                    8. Design a brochure for a restaurant
                                </h3>
                                <div class="flex items-center gap-2 text-[11px] text-slate-400 mt-0.5">
                                    <span>Cinema 4D</span>
                                    <span>•</span>
                                    <span class="flex items-center gap-1">
                                        <span>Level:</span>
                                        <span class="inline-flex gap-0.5">
                                            <span class="w-3 h-1.5 rounded-full bg-[#FE774C]"></span>
                                            <span class="w-3 h-1.5 rounded-full bg-[#FE774C]"></span>
                                            <span class="w-3 h-1.5 rounded-full bg-[#FE774C]"></span>
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <span class="text-slate-400 hover:text-slate-600">
                            ▼
                        </span>
                    </div>

                </div>
            </div>

        </div>

        {{-- ================================================================
             AREA KANAN: Right Panel (Profile, Streak, Calendar, Schedule)
             Sesuai panel kanan image_013.jpg
             ================================================================ --}}
        <div class="w-full xl:w-80 flex-shrink-0 space-y-6">
            
            {{-- 1. Profil Pengguna --}}
            <div class="lms-card p-5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img class="w-12 h-12 rounded-full object-cover ring-2 ring-slate-100" src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=128&h=128&fit=crop&crop=faces" alt="Avatar">
                    <div class="min-w-0">
                        <h3 class="text-sm font-extrabold text-slate-900 truncate">Yuliia Kirieleva</h3>
                        <p class="text-[11px] text-slate-400 truncate font-mono">kirielevzza@gmail.com</p>
                    </div>
                </div>
                <button class="w-7 h-7 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center text-xs hover:bg-slate-200">
                    ▲
                </button>
            </div>

            {{-- 2. Streak Counter (Flame Gauge) --}}
            <div class="lms-card p-5 text-center">
                <div class="relative w-36 h-20 mx-auto overflow-hidden">
                    {{-- Semi circle rainbow gauge --}}
                    <svg class="w-36 h-36 -rotate-90 origin-center" viewBox="0 0 100 100">
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#F1F5F9" stroke-width="8"/>
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#5DD299" stroke-width="8" stroke-dasharray="125 250" stroke-linecap="round"/>
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#FFC152" stroke-width="8" stroke-dasharray="70 250" stroke-linecap="round"/>
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#FE774C" stroke-width="8" stroke-dasharray="30 250" stroke-linecap="round"/>
                    </svg>
                    {{-- Center Flame --}}
                    <div class="absolute inset-x-0 bottom-0 flex flex-col items-center justify-center">
                        <span class="text-2xl">🔥</span>
                    </div>
                </div>
                <div class="mt-1">
                    <span class="text-xl font-extrabold text-slate-900">4</span>
                    <p class="text-[11px] text-slate-400 font-semibold">days of continuous study</p>
                </div>
            </div>

            {{-- 3. Mini Calendar Strip --}}
            <div class="lms-card p-5">
                <div class="flex items-center justify-between mb-3.5 text-xs font-bold text-slate-800">
                    <button class="text-slate-400 hover:text-slate-600">‹</button>
                    <span>May 2026</span>
                    <button class="text-slate-400 hover:text-slate-600">›</button>
                </div>

                <div class="grid grid-cols-6 gap-1 text-center">
                    <div class="p-1.5 rounded-lg text-slate-400 text-[10px]">
                        <span>Mon</span>
                        <span class="block font-bold text-slate-700 text-xs mt-0.5">16</span>
                    </div>
                    <div class="p-1.5 rounded-lg text-slate-400 text-[10px]">
                        <span>Tue</span>
                        <span class="block font-bold text-slate-700 text-xs mt-0.5">17</span>
                    </div>
                    <div class="p-1.5 rounded-lg text-slate-400 text-[10px]">
                        <span>Wed</span>
                        <span class="block font-bold text-slate-700 text-xs mt-0.5">18</span>
                    </div>
                    {{-- Active Day Pill (Mint) --}}
                    <div class="p-1.5 rounded-xl bg-[#5DD299] text-white text-[10px] shadow-sm">
                        <span>Thu</span>
                        <span class="block font-extrabold text-white text-xs mt-0.5">19</span>
                    </div>
                    <div class="p-1.5 rounded-lg text-slate-400 text-[10px]">
                        <span>Fri</span>
                        <span class="block font-bold text-slate-700 text-xs mt-0.5">20</span>
                    </div>
                    <div class="p-1.5 rounded-lg text-slate-400 text-[10px]">
                        <span>Sat</span>
                        <span class="block font-bold text-slate-700 text-xs mt-0.5">21</span>
                    </div>
                </div>
            </div>

            {{-- 4. Courses Progress (4-Box Bento Grid) --}}
            <div class="lms-card p-5">
                <h3 class="text-xs font-extrabold text-slate-800 mb-3.5">Courses Progress</h3>
                
                <div class="grid grid-cols-2 gap-3 text-xs">
                    {{-- Box 1 --}}
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="font-extrabold text-slate-900 text-sm">75/110</span>
                        <p class="text-[10px] text-slate-400 mt-0.5">Visited lectures</p>
                        <div class="w-full h-1 bg-slate-200 rounded-full mt-2 overflow-hidden">
                            <div class="h-full bg-[#5DD299] rounded-full" style="width: 68%"></div>
                        </div>
                    </div>

                    {{-- Box 2 --}}
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="font-extrabold text-slate-900 text-sm">15/25</span>
                        <p class="text-[10px] text-slate-400 mt-0.5">Completed HW</p>
                    </div>

                    {{-- Box 3 --}}
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="font-extrabold text-slate-900 text-sm">300/1000</span>
                        <p class="text-[10px] text-slate-400 mt-0.5">Bonuses</p>
                    </div>

                    {{-- Box 4 --}}
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="font-extrabold text-slate-900 text-sm">8/10</span>
                        <p class="text-[10px] text-slate-400 mt-0.5">Certificates</p>
                    </div>
                </div>
            </div>

            {{-- 5. Schedule --}}
            <div class="lms-card p-5">
                <div class="flex items-center justify-between mb-3.5">
                    <h3 class="text-xs font-extrabold text-slate-800">Schedule</h3>
                    <button class="text-[11px] font-bold text-slate-400 hover:text-slate-600">See all</button>
                </div>

                <div class="space-y-2.5">
                    {{-- Schedule Item 1 --}}
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100 hover:border-slate-200 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-sky-50 text-[#66A7F2] flex items-center justify-center font-bold text-xs">
                                👥
                            </div>
                            <div>
                                <p class="text-xs font-extrabold text-slate-800">Vebinar</p>
                                <p class="text-[10px] text-slate-400">09:00 - 10:00 | Graphic design</p>
                            </div>
                        </div>
                        <span class="text-slate-400 text-xs">›</span>
                    </div>

                    {{-- Schedule Item 2 --}}
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100 hover:border-slate-200 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-amber-50 text-[#925400] flex items-center justify-center font-bold text-xs font-mono">
                                4D
                            </div>
                            <div>
                                <p class="text-xs font-extrabold text-slate-800">Homework 9</p>
                                <p class="text-[10px] text-slate-400">23:00 | Cinema 4D</p>
                            </div>
                        </div>
                        <span class="text-slate-400 text-xs">›</span>
                    </div>
                </div>
            </div>

            {{-- 6. Notification Popup Banner (Dark #1E1E26) --}}
            <div class="p-4 rounded-2xl bg-[#1E1E26] text-white shadow-lg border border-[#363644] flex items-start justify-between gap-3">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-xl bg-[#252530] text-[#FFC152] flex items-center justify-center text-sm flex-shrink-0">
                        🔔
                    </div>
                    <div>
                        <p class="text-xs font-extrabold text-white">Vebinar starting</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">May 18 at 09:00 | Graphic design</p>
                    </div>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-white text-sm">
                    &times;
                </button>
            </div>

        </div>

    </div>

</x-layout>