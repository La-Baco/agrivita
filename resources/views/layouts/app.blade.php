<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - AgriVita Sumenep</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN Fallback & Styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            300: '#86efac',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
                            950: '#052e16',
                        },
                        drought: {
                            green: '#10b981',
                            yellow: '#f59e0b',
                            orange: '#f97316',
                            red: '#ef4444',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Leaflet GIS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        .leaflet-container { font-family: inherit; }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
        }
        /* Hilangkan animasi scrollbar di sisi kanan navbar tanpa mengorbankan fungsi scroll */
        .sidebar-nav::-webkit-scrollbar,
        aside nav::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
            background: transparent !important;
        }
        .sidebar-nav,
        aside nav {
            -ms-overflow-style: none !important; /* IE dan Edge */
            scrollbar-width: none !important; /* Firefox */
            scroll-behavior: auto !important; /* Hilangkan animasi scrolling */
        }
    </style>
    @stack('styles')
</head>
<body class="h-full flex overflow-hidden text-slate-800">

    <!-- Sidebar Navigation -->
    <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col flex-shrink-0 border-r border-slate-800 select-none">
        <!-- Logo & Branding -->
        <div class="p-5 border-b border-slate-800 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand-500 to-emerald-700 flex items-center justify-center text-white font-bold shadow-lg shadow-brand-900/40">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-white tracking-tight flex items-center">
                        AgriVita <span class="ml-1 text-[10px] px-1.5 py-0.5 rounded bg-brand-500/20 text-brand-400 font-semibold border border-brand-500/30">GIS</span>
                    </h1>
                    <p class="text-[11px] text-slate-400 font-medium">Kab. Sumenep, Madura</p>
                </div>
            </div>
        </div>

        <!-- System Research Badge -->
        <div class="mx-3 mt-3 px-3 py-2 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-300 text-[11px] leading-tight">
            <span class="font-bold flex items-center">
                <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse mr-1.5"></span>
                PROTOTIPE RISET
            </span>
            <p class="mt-0.5 text-amber-200/80 text-[10px]">Rule-Based & Spasial Terbuka</p>
        </div>

        <!-- Navigation Menu (Scrollable tanpa animasi scrollbar kanan) -->
        <nav class="sidebar-nav flex-1 overflow-y-auto px-3 py-4 space-y-1 text-sm font-medium">
            <a href="{{ route('dashboard') }}" class="flex items-center px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('dashboard') ? 'bg-brand-600 text-white font-semibold shadow-sm' : 'hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 text-slate-400 {{ request()->routeIs('dashboard') ? '!text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard Ringkasan
            </a>

            <a href="{{ route('map.index') }}" class="flex items-center px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('map.*') ? 'bg-brand-600 text-white font-semibold shadow-sm' : 'hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 text-slate-400 {{ request()->routeIs('map.*') ? '!text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                </svg>
                Peta Monitoring Spasial
            </a>

            <a href="{{ route('lands.index') }}" class="flex items-center px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('lands.*') ? 'bg-brand-600 text-white font-semibold shadow-sm' : 'hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 text-slate-400 {{ request()->routeIs('lands.*') ? '!text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                Manajemen Lahan
            </a>

            <div class="pt-3 pb-1 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                Parameter & Analisis
            </div>

            <a href="{{ route('rainfall.index') }}" class="flex items-center px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('rainfall.*') ? 'bg-brand-600 text-white font-semibold' : 'hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z"/>
                </svg>
                Curah Hujan (CHIRPS/AWS)
            </a>

            <a href="{{ route('weather.index') }}" class="flex items-center px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('weather.*') ? 'bg-brand-600 text-white font-semibold' : 'hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                Prakiraan Cuaca BMKG
            </a>

            <a href="{{ route('ndvi.index') }}" class="flex items-center px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('ndvi.*') ? 'bg-brand-600 text-white font-semibold' : 'hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                </svg>
                Satelit Sentinel-2 & NDVI
            </a>

            <a href="{{ route('water-balance.index') }}" class="flex items-center px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('water-balance.*') ? 'bg-brand-600 text-white font-semibold' : 'hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                </svg>
                Neraca Air Lahan (ETc)
            </a>

            <div class="pt-3 pb-1 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                Peringatan & Rekomendasi
            </div>

            <a href="{{ route('drought.index') }}" class="flex items-center px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('drought.*') ? 'bg-brand-600 text-white font-semibold' : 'hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                Analisis Kekeringan
            </a>

            <a href="{{ route('water-needs.index') }}" class="flex items-center px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('water-needs.*') ? 'bg-brand-600 text-white font-semibold' : 'hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                </svg>
                Kebutuhan Air Tanaman
            </a>

            <a href="{{ route('priority.index') }}" class="flex items-center px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('priority.*') ? 'bg-brand-600 text-white font-semibold' : 'hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                Prioritas Alokasi Air
            </a>

            <div class="pt-3 pb-1 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                Pelaporan & Konfigurasi
            </div>

            <a href="{{ route('history.index') }}" class="flex items-center px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('history.*') ? 'bg-brand-600 text-white font-semibold' : 'hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Riwayat & Tren Lahan
            </a>

            <a href="{{ route('reports.index') }}" class="flex items-center px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('reports.*') ? 'bg-brand-600 text-white font-semibold' : 'hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Laporan Eksekutif
            </a>

            <a href="{{ route('settings.index') }}" class="flex items-center px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('settings.*') ? 'bg-brand-600 text-white font-semibold' : 'hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Parameter & Koefisien (Kc)
            </a>
        </nav>

        <!-- User Profile & Logout -->
        <div class="p-4 border-t border-slate-800 bg-slate-950/40">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3 truncate">
                    <div class="w-8 h-8 rounded-full bg-brand-600/30 text-brand-400 flex items-center justify-center font-bold text-xs border border-brand-500/40">
                        {{ substr(auth()->user()->name ?? 'P', 0, 1) }}
                    </div>
                    <div class="truncate">
                        <p class="text-xs font-semibold text-white truncate">{{ auth()->user()->name ?? 'Peneliti' }}</p>
                        <p class="text-[11px] text-slate-400 truncate">{{ auth()->user()->email ?? 'admin@agrivita.id' }}</p>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" title="Keluar" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-slate-800 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-50">

        <!-- Top Header Notification Bar -->
        <header class="bg-white border-b border-slate-200 shadow-sm z-10 flex-shrink-0">
            <!-- Disclaimer Banner -->
            @php
                $isSystemDemo = config('app.data_mode') === 'DEMO';
            @endphp
            @if ($isSystemDemo)
            <div class="bg-gradient-to-r from-amber-500/10 via-brand-500/10 to-blue-500/10 border-b border-amber-200/60 px-6 py-2 flex items-center justify-between text-xs">
                <div class="flex items-center space-x-2 text-slate-700">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                        DATA DEMO
                    </span>
                    <span class="font-medium">
                        Sistem Pemantauan Berbasis Spasial (Sumenep, Jatim) • Mode Demo Simulasi • Perhitungan transparan berbasis rumus agronomis.
                    </span>
                </div>
                <div class="hidden sm:flex items-center space-x-3 text-slate-500 text-[11px]">
                    <span class="text-amber-700 font-medium">Mode: Simulasi</span>
                    <span>•</span>
                    <span class="text-slate-600">Status: Kalibrasi Demo</span>
                </div>
            </div>
            @else
            <div class="bg-gradient-to-r from-emerald-500/10 via-brand-500/10 to-blue-500/10 border-b border-emerald-200/60 px-6 py-2 flex items-center justify-between text-xs">
                <div class="flex items-center space-x-2 text-slate-700">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-xs">
                        DATA RIIL / LIVE
                    </span>
                    <span class="font-medium">
                        Sistem Pemantauan Berbasis Spasial (Sumenep, Jatim) • Terhubung ke Data Cuaca & Presipitasi Aktual Stasiun / Open-Meteo.
                    </span>
                </div>
                <div class="hidden sm:flex items-center space-x-3 text-slate-500 text-[11px]">
                    <span class="flex items-center space-x-1.5 text-emerald-700 font-semibold">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Status: Data Riil Terhubung</span>
                    </span>
                </div>
            </div>
            @endif

            <!-- Page Title Bar -->
            <div class="px-6 py-3.5 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 tracking-tight">@yield('page_title', 'Dashboard')</h2>
                    <p class="text-xs text-slate-500 mt-0.5">@yield('page_subtitle', 'Sistem Pemantauan Hujan, Kekeringan, Vegetasi, dan Kebutuhan Air Lahan Pertanian')</p>
                </div>
                <div class="flex items-center space-x-3">
                    @yield('header_actions')
                    <div class="text-right hidden md:block">
                        <p class="text-xs font-semibold text-slate-700">{{ now()->translatedFormat('l, d F Y') }}</p>
                        <p class="text-[11px] text-slate-500">Stasiun Meteorologi Kalianget, Sumenep</p>
                    </div>
                </div>
            </div>
        </header>

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="mx-6 mt-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900">&times;</button>
            </div>
        @endif

        @if (session('error'))
            <div class="mx-6 mt-4 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900">&times;</button>
            </div>
        @endif

        <!-- Dynamic Main Content Scrollable -->
        <main class="flex-1 overflow-y-auto p-6">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
