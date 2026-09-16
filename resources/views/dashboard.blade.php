@extends('layouts.app')

@section('title', 'Dashboard Riset Spasial')
@section('page_title', 'Dashboard Riset Agrikultur & Neraca Air')
@section('page_subtitle', 'Pemantauan Spasial Presipitasi NASA GPM, Citra Sentinel-2 NDVI, Neraca Air FAO-56, & Prakiraan BMKG di Kab. Sumenep')

@section('header_actions')
<div class="flex items-center space-x-2.5">
    <a href="{{ route('weather.index') }}" class="inline-flex items-center px-3.5 py-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs transition">
        <svg class="w-4 h-4 mr-1.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
        Prakiraan BMKG
    </a>
    <a href="{{ route('map.index') }}" class="inline-flex items-center px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold shadow-xs transition">
        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
        </svg>
        Peta GIS Interaktif
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- 0. Live BMKG Weather Broadcast Banner -->
    <div class="bg-gradient-to-r from-sky-900 via-indigo-900 to-slate-900 rounded-2xl p-5 text-white shadow-md relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-sky-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-start sm:items-center space-x-4">
                <div class="w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center shrink-0 shadow-inner">
                    <span class="text-3xl">🌤️</span>
                </div>
                <div>
                    <div class="flex items-center space-x-2.5">
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-sky-400/20 text-sky-200 border border-sky-400/30 tracking-wide uppercase">
                            BMKG Open Data Live
                        </span>
                        <span class="text-xs text-sky-200/80">Stasiun Meteorologi Kab. Sumenep</span>
                    </div>
                    <h3 class="text-lg font-bold text-white mt-1">
                        {{ $bmkgLatest?->weather ?? 'Cerah Berawan' }} • {{ number_format($bmkgLatest?->temperature ?? 30, 1) }}°C
                    </h3>
                    <p class="text-xs text-slate-300 mt-0.5">
                        Kelembapan: <strong>{{ number_format($bmkgLatest?->humidity ?? 70, 0) }}%</strong> • Angin: <strong>{{ number_format($bmkgLatest?->wind_speed ?? 12, 1) }} km/jam ({{ $bmkgLatest?->wind_direction ?? 'Timur' }})</strong> • Akumulasi Hujan 3 Hari: <strong class="text-sky-300">{{ number_format($bmkg3DayRain, 1) }} mm</strong>
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-3 self-end md:self-center">
                <div class="text-right hidden sm:block">
                    <span class="text-[11px] text-slate-300 block">Status Data</span>
                    <span class="inline-flex items-center text-xs font-semibold text-emerald-400">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse mr-1.5"></span>
                        Terhubung Real-time
                    </span>
                </div>
                <a href="{{ route('weather.index') }}" class="px-3.5 py-2 rounded-xl bg-white/15 hover:bg-white/25 border border-white/20 text-white text-xs font-semibold transition whitespace-nowrap">
                    Rincian BMKG &rarr;
                </a>
            </div>
        </div>
    </div>

    <!-- 1. Top KPI Summary Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Card 1: Total Lahan -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs hover:border-slate-300 transition flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Lahan Riset</p>
                <h3 class="text-2xl font-extrabold text-slate-900 mt-1 font-mono">{{ $totalLahan }} Lahan</h3>
                <p class="text-xs text-slate-500 mt-1">Cakupan Luas: <span class="font-bold text-slate-800">{{ number_format($totalLuas, 2) }} Ha</span></p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
        </div>

        <!-- Card 2: Status Kekeringan -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs hover:border-slate-300 transition">
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Risiko Kekeringan</p>
            <div class="grid grid-cols-4 gap-1.5">
                <div class="text-center p-1.5 rounded-lg bg-rose-50 border border-rose-100">
                    <span class="text-xs font-bold font-mono text-rose-700 block">{{ $statusCounts['MERAH'] ?? 0 }}</span>
                    <span class="text-[9px] font-semibold text-rose-600">Kritis</span>
                </div>
                <div class="text-center p-1.5 rounded-lg bg-amber-50 border border-amber-100">
                    <span class="text-xs font-bold font-mono text-amber-700 block">{{ $statusCounts['ORANYE'] ?? 0 }}</span>
                    <span class="text-[9px] font-semibold text-amber-600">Waspada</span>
                </div>
                <div class="text-center p-1.5 rounded-lg bg-yellow-50 border border-yellow-100">
                    <span class="text-xs font-bold font-mono text-yellow-700 block">{{ $statusCounts['KUNING'] ?? 0 }}</span>
                    <span class="text-[9px] font-semibold text-yellow-600">Ringan</span>
                </div>
                <div class="text-center p-1.5 rounded-lg bg-emerald-50 border border-emerald-100">
                    <span class="text-xs font-bold font-mono text-emerald-700 block">{{ $statusCounts['HIJAU'] ?? 0 }}</span>
                    <span class="text-[9px] font-semibold text-emerald-600">Aman</span>
                </div>
            </div>
            <p class="text-[10px] text-slate-400 mt-2">Dianalisis berbasis 4 pilar aturan transparan</p>
        </div>

        <!-- Card 3: Rata-rata NDVI -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs hover:border-slate-300 transition flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Rata-rata NDVI Sentinel-2</p>
                <h3 class="text-2xl font-extrabold text-emerald-700 mt-1 font-mono">{{ number_format($latestNdviAvg ?? 0.55, 3) }}</h3>
                <p class="text-xs text-slate-500 mt-1">Kondisi: <span class="font-semibold text-emerald-700">Vegetasi Produktif</span></p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 border border-teal-100 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                </svg>
            </div>
        </div>

        <!-- Card 4: Kebutuhan Kuota Air -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs hover:border-slate-300 transition flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Kebutuhan Irigasi</p>
                <h3 class="text-2xl font-extrabold text-blue-700 mt-1 font-mono">{{ number_format($totalWaterNeed ?? 0, 1) }} mm</h3>
                <p class="text-xs text-slate-500 mt-1">Presipitasi 7 Hari: <span class="font-bold text-slate-800">{{ number_format($rainfall7d ?? 0, 1) }} mm</span></p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- 2. GIS Map & Priority Alert Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Leaflet Interactive GIS Preview (2 Cols) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden flex flex-col">
            <div class="p-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-2 bg-slate-50/70">
                <div class="flex items-center space-x-2.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 ring-4 ring-emerald-100"></span>
                    <h4 class="font-bold text-slate-800 text-sm">Peta Sebaran Lahan & Status Spasial Sumenep</h4>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="inline-flex rounded-lg border border-slate-200 p-0.5 bg-slate-100">
                        <button type="button" id="dashBtnOsm" class="px-2.5 py-1 rounded-md bg-white text-slate-900 shadow-xs font-semibold text-[11px] transition">Street</button>
                        <button type="button" id="dashBtnSat" class="px-2.5 py-1 rounded-md text-slate-600 hover:text-slate-900 font-semibold text-[11px] transition">Satelit</button>
                    </div>
                    <a href="{{ route('map.index') }}" class="ml-1 text-xs text-emerald-600 hover:text-emerald-700 font-semibold inline-flex items-center">
                        GIS Penuh &rarr;
                    </a>
                </div>
            </div>
            <div class="relative w-full">
                <div id="dashboardMap" class="w-full bg-slate-100 relative z-0" style="height: 380px; min-height: 380px; width: 100%;"></div>
                
                <!-- Floating Minimal Legend -->
                <div class="absolute bottom-3 left-3 z-[400] bg-white/95 backdrop-blur-xs px-3 py-1.5 rounded-xl border border-slate-200 shadow-md text-[11px] flex items-center space-x-3 pointer-events-none">
                    <span class="font-bold text-slate-700">Status:</span>
                    <span class="inline-flex items-center text-slate-600"><span class="w-2.5 h-2.5 rounded-full bg-rose-500 mr-1.5"></span> Kritis</span>
                    <span class="inline-flex items-center text-slate-600"><span class="w-2.5 h-2.5 rounded-full bg-amber-500 mr-1.5"></span> Waspada</span>
                    <span class="inline-flex items-center text-slate-600"><span class="w-2.5 h-2.5 rounded-full bg-yellow-400 mr-1.5"></span> Ringan</span>
                    <span class="inline-flex items-center text-slate-600"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500 mr-1.5"></span> Aman</span>
                </div>
            </div>
            <div class="p-3 bg-slate-50 border-t border-slate-100 text-xs text-slate-500 flex items-center justify-between">
                <span>Klik poligon lahan pada peta untuk melihat data agronomis detail.</span>
                <span class="font-mono text-[11px] text-slate-400">Pusat: Kab. Sumenep (-7.016, 113.864)</span>
            </div>
        </div>

        <!-- Right: Lahan Prioritas Alokasi Air (1 Col) -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center space-x-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-ping"></span>
                        <h4 class="font-bold text-slate-800 text-sm">Prioritas Alokasi Air Segera</h4>
                    </div>
                    <a href="{{ route('priority.index') }}" class="text-xs text-emerald-600 hover:text-emerald-700 font-semibold">Semua &rarr;</a>
                </div>

                <div class="mt-3.5 space-y-3 overflow-y-auto max-h-76 pr-1">
                    @php
                        $criticalLands = $mapLands->filter(function($l) {
                            $s = $l->droughtAnalyses->first()?->status;
                            return in_array($s, ['MERAH', 'ORANYE']);
                        });
                    @endphp

                    @forelse ($criticalLands as $land)
                        @php
                            $analysis = $land->droughtAnalyses->first();
                            $isRed = $analysis?->status === 'MERAH';
                        @endphp
                        <div class="p-3.5 rounded-xl border {{ $isRed ? 'bg-rose-50/70 border-rose-200' : 'bg-amber-50/70 border-amber-200' }} hover:shadow-xs transition">
                            <div class="flex items-start justify-between">
                                <div>
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase {{ $isRed ? 'bg-rose-200 text-rose-800' : 'bg-amber-200 text-amber-800' }}">
                                        {{ $analysis?->status_label ?? 'Risiko Kekeringan' }}
                                    </span>
                                    <h5 class="font-bold text-slate-900 text-xs mt-1.5">{{ $land->nama_lahan }}</h5>
                                    <p class="text-[11px] text-slate-600">{{ $land->desa }}, Kec. {{ $land->kecamatan }}</p>
                                </div>
                                <span class="font-mono text-xs font-bold {{ $isRed ? 'text-rose-700' : 'text-amber-700' }} whitespace-nowrap">
                                    Defisit {{ abs($analysis?->water_balance_value ?? 0) }} mm
                                </span>
                            </div>
                            <div class="mt-2 text-[11px] text-slate-600 flex items-center justify-between border-t border-slate-200/60 pt-2">
                                <span>Komoditas: <strong class="text-slate-800">{{ $land->jenis_tanaman }}</strong></span>
                                <a href="{{ route('lands.show', $land->id) }}" class="font-semibold text-emerald-700 hover:underline">Detail &rarr;</a>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-400 text-xs">
                            <span class="text-2xl block mb-2">🌾</span>
                            Seluruh lahan terpantau dalam kondisi neraca air aman.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 mt-3 text-center">
                <a href="{{ route('priority.index') }}" class="w-full py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold inline-block transition">
                    Buka Matriks Prioritas Irigasi &rarr;
                </a>
            </div>
        </div>
    </div>

    <!-- 3. Trend Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Chart 1: Curah Hujan 30 Hari -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h4 class="font-bold text-slate-800 text-sm">Fluktuasi Presipitasi Harian Aktual (30 Hari)</h4>
                    <p class="text-xs text-slate-400">Sumber: Satelit NASA GPM & Stasiun Meteorologi Kab. Sumenep</p>
                </div>
                <span class="px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 font-mono text-xs font-bold border border-blue-100">mm / hari</span>
            </div>
            <div class="h-64">
                <canvas id="rainfallChart"></canvas>
            </div>
        </div>

        <!-- Chart 2: NDVI Sentinel-2 Trend -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h4 class="font-bold text-slate-800 text-sm">Dinamika Indeks Vegetasi NDVI (Sentinel-2 60 Hari)</h4>
                    <p class="text-xs text-slate-400">Formula Reflektansi: (NIR Band-8 - Red Band-4) / (NIR + Red)</p>
                </div>
                <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-mono text-xs font-bold border border-emerald-100">Skala 0 - 1</span>
            </div>
            <div class="h-64">
                <canvas id="ndviChart"></canvas>
            </div>
        </div>
    </div>

    <!-- 4. Daftar 10 Lahan Pertanian Sumenep -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3 bg-slate-50/50">
            <div>
                <h4 class="font-bold text-slate-800 text-sm">Daftar Seluruh Lahan Pertanian Riset (Kabupaten Sumenep)</h4>
                <p class="text-xs text-slate-500 mt-0.5">Integrasi poligon GeoJSON spasial dengan kondisi agronomis terkini</p>
            </div>
            <a href="{{ route('lands.create') }}" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold shadow-xs transition flex items-center">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Lahan Baru
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-4">Kode & Nama Lahan</th>
                        <th class="py-3.5 px-4">Kecamatan / Desa</th>
                        <th class="py-3.5 px-4">Komoditas & Umur</th>
                        <th class="py-3.5 px-4">Luas Lahan</th>
                        <th class="py-3.5 px-4">NDVI Terkini</th>
                        <th class="py-3.5 px-4">Status Kekeringan</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach ($mapLands as $land)
                        @php
                            $drought = $land->droughtAnalyses->first();
                            $status = $drought?->status ?? 'HIJAU';
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900">{{ $land->nama_lahan }}</div>
                                <span class="font-mono text-[10px] text-slate-400">{{ $land->kode_lahan }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-slate-800">{{ $land->kecamatan }}</div>
                                <div class="text-slate-400 text-[11px]">{{ $land->desa }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-emerald-800">{{ $land->jenis_tanaman }}</div>
                                <div class="text-slate-400 text-[11px]">{{ $land->umur_tanaman }} HST (Hari Setelah Tanam)</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-medium">
                                {{ $land->luas }} Ha
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-mono font-bold text-emerald-700">{{ $drought?->ndvi_value ?? '-' }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-bold {{ $status === 'MERAH' ? 'bg-rose-100 text-rose-800 border border-rose-200' : ($status === 'ORANYE' ? 'bg-amber-100 text-amber-800 border border-amber-200' : ($status === 'KUNING' ? 'bg-yellow-100 text-yellow-800 border border-yellow-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-200')) }}">
                                    {{ $drought?->status_label ?? 'Air Cukup' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-2">
                                <a href="{{ route('lands.show', $land->id) }}" class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition inline-block">
                                    Dossier
                                </a>
                                <a href="{{ route('drought.show', $land->id) }}" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold transition inline-block">
                                    Audit Risiko
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- 1. Inisialisasi Peta GIS Leaflet ---
        const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '&copy; OpenStreetMap'
        });

        const satLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 18,
            attribution: 'Tiles &copy; Esri'
        });

        const map = L.map('dashboardMap', {
            center: [-7.016, 113.864],
            zoom: 10,
            layers: [osmLayer]
        });

        // Basemap Switcher
        const btnOsm = document.getElementById('dashBtnOsm');
        const btnSat = document.getElementById('dashBtnSat');

        if (btnOsm && btnSat) {
            btnOsm.addEventListener('click', function() {
                map.removeLayer(satLayer);
                map.addLayer(osmLayer);
                btnOsm.className = "px-2.5 py-1 rounded-md bg-white text-slate-900 shadow-xs font-semibold text-[11px] transition";
                btnSat.className = "px-2.5 py-1 rounded-md text-slate-600 hover:text-slate-900 font-semibold text-[11px] transition";
            });

            btnSat.addEventListener('click', function() {
                map.removeLayer(osmLayer);
                map.addLayer(satLayer);
                btnSat.className = "px-2.5 py-1 rounded-md bg-white text-slate-900 shadow-xs font-semibold text-[11px] transition";
                btnOsm.className = "px-2.5 py-1 rounded-md text-slate-600 hover:text-slate-900 font-semibold text-[11px] transition";
            });
        }

        // Render GeoJSON Lahan
        const geojsonData = {!! json_encode($mapGeoJson) !!};

        if (geojsonData && geojsonData.features && geojsonData.features.length > 0) {
            const layer = L.geoJSON(geojsonData, {
                style: function(feature) {
                    const color = feature.properties.drought_color || '#10b981';
                    return {
                        color: color,
                        weight: 2.5,
                        opacity: 0.9,
                        fillColor: color,
                        fillOpacity: 0.45
                    };
                },
                onEachFeature: function(feature, layer) {
                    const p = feature.properties;
                    const isDeficit = Number(p.water_balance) < 0;
                    const popupContent = `
                        <div class="p-1 font-sans">
                            <div class="flex items-center justify-between gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold" style="background-color: ${p.drought_color}20; color: ${p.drought_color}; border: 1px solid ${p.drought_color}">
                                    ${p.drought_label}
                                </span>
                                <span class="text-[10px] font-mono text-slate-400 font-bold">${p.kode_lahan}</span>
                            </div>
                            <h6 class="font-bold text-slate-900 text-sm mt-1.5">${p.nama_lahan}</h6>
                            <p class="text-xs text-slate-500">${p.desa}, Kec. ${p.kecamatan}</p>
                            <div class="mt-2 text-xs space-y-1 bg-slate-50 p-2 rounded-lg border border-slate-100">
                                <div class="flex justify-between"><span>Tanaman:</span> <strong class="text-slate-800">${p.jenis_tanaman} (${p.umur_tanaman} HST)</strong></div>
                                <div class="flex justify-between"><span>Luas:</span> <strong class="text-slate-800">${p.luas} Ha</strong></div>
                                <div class="flex justify-between"><span>NDVI:</span> <strong class="font-mono text-emerald-600">${p.ndvi ?? '-'}</strong></div>
                                <div class="flex justify-between"><span>Neraca Air:</span> <strong class="font-mono ${isDeficit ? 'text-rose-600' : 'text-emerald-600'}">${p.water_balance ?? 0} mm</strong></div>
                            </div>
                            <div class="mt-2 pt-2 border-t border-slate-200 text-right">
                                <a href="${p.url}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700">Buka Dossier Lahan &rarr;</a>
                            </div>
                        </div>
                    `;
                    layer.bindPopup(popupContent);
                }
            }).addTo(map);

            map.fitBounds(layer.getBounds(), { padding: [25, 25] });
        }

        // Invalidate map size after DOM is painted to ensure perfect tile rendering
        setTimeout(() => {
            map.invalidateSize();
        }, 200);

        // --- 2. Chart Curah Hujan 30 Hari ---
        const rainLabels = {!! json_encode($rainfallChartData->pluck('observation_date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))) !!};
        const rainValues = {!! json_encode($rainfallChartData->pluck('avg_rainfall')->map(fn($v) => round($v, 1))) !!};

        const ctxRain = document.getElementById('rainfallChart').getContext('2d');
        new Chart(ctxRain, {
            type: 'bar',
            data: {
                labels: rainLabels,
                datasets: [{
                    label: 'Curah Hujan Harian Rata-rata (mm)',
                    data: rainValues,
                    backgroundColor: '#3b82f6',
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(c) { return `${c.parsed.y} mm`; }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Milimeter (mm)' }
                    }
                }
            }
        });

        // --- 3. Chart Dinamika NDVI 60 Hari ---
        const ndviLabels = {!! json_encode($ndviChartData->pluck('observation_date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))) !!};
        const ndviValues = {!! json_encode($ndviChartData->pluck('avg_ndvi')->map(fn($v) => round($v, 3))) !!};

        const ctxNdvi = document.getElementById('ndviChart').getContext('2d');
        new Chart(ctxNdvi, {
            type: 'line',
            data: {
                labels: ndviLabels,
                datasets: [{
                    label: 'Indeks NDVI Rata-rata',
                    data: ndviValues,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 4,
                    pointBackgroundColor: '#047857'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(c) { return `NDVI: ${c.parsed.y}`; }
                        }
                    }
                },
                scales: {
                    y: {
                        min: 0,
                        max: 1.0,
                        title: { display: true, text: 'Indeks NDVI (0 - 1)' }
                    }
                }
            }
        });
    });
</script>
@endpush
