@extends('layouts.app')

@section('title', 'Dossier Lahan - ' . $land->nama_lahan)
@section('page_title', $land->nama_lahan)
@section('page_subtitle', 'Kode: ' . $land->kode_lahan . ' • Desa ' . $land->desa . ', Kec. ' . $land->kecamatan . ', Kab. Sumenep')

@section('header_actions')
<div class="flex items-center space-x-2">
    <a href="{{ route('lands.edit', $land->id) }}" class="inline-flex items-center px-3 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold border border-blue-200 transition">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
        </svg>
        Edit Lahan
    </a>
    <a href="{{ route('lands.index') }}" class="inline-flex items-center px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
        &larr; Daftar Lahan
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- 1. Top Analytical Status Ribbon -->
    @php
        $status = $latestDrought?->status ?? 'HIJAU';
        $statusColors = [
            'MERAH' => ['bg' => 'bg-rose-50', 'border' => 'border-rose-300', 'badge' => 'bg-rose-600 text-white', 'text' => 'text-rose-900'],
            'ORANYE' => ['bg' => 'bg-amber-50', 'border' => 'border-amber-300', 'badge' => 'bg-orange-500 text-white', 'text' => 'text-amber-900'],
            'KUNING' => ['bg' => 'bg-yellow-50', 'border' => 'border-yellow-300', 'badge' => 'bg-yellow-500 text-white', 'text' => 'text-yellow-900'],
            'HIJAU' => ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-300', 'badge' => 'bg-emerald-600 text-white', 'text' => 'text-emerald-900'],
        ];
        $theme = $statusColors[$status] ?? $statusColors['HIJAU'];
    @endphp

    <div class="p-5 rounded-2xl border {{ $theme['bg'] }} {{ $theme['border'] }} shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center space-x-3">
                <span class="px-3 py-1.5 rounded-xl font-extrabold text-xs uppercase tracking-wider {{ $theme['badge'] }}">
                    {{ $latestDrought?->status_label ?? 'Air Cukup (Aman)' }}
                </span>
                <div>
                    <h3 class="text-base font-bold {{ $theme['text'] }}">
                        Status Risiko Kekeringan Terkini: {{ $status }}
                    </h3>
                    <p class="text-xs text-slate-600 mt-0.5">
                        Tanggal Analisis: {{ $latestDrought ? \Carbon\Carbon::parse($latestDrought->analysis_date)->translatedFormat('d F Y') : now()->translatedFormat('d F Y') }}
                        • Skor Gabungan: <strong class="font-mono">{{ $latestDrought?->metrics_json['total_score'] ?? '0' }}/100</strong>
                    </p>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <a href="{{ route('drought.show', $land->id) }}" class="px-3 py-1.5 rounded-lg bg-white hover:bg-slate-50 text-slate-800 text-xs font-semibold border border-slate-200 shadow-sm transition">
                    Audit Rumus Penilaian &rarr;
                </a>
                <a href="{{ route('history.show', $land->id) }}" class="px-3 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold transition">
                    Riwayat Komprehensif
                </a>
            </div>
        </div>

        @if ($latestDrought?->analysis_notes)
            <div class="mt-3 pt-3 border-t {{ $theme['border'] }} text-xs {{ $theme['text'] }} leading-relaxed">
                <strong>Catatan Analisis Aturan Terbuka:</strong> {{ $latestDrought->analysis_notes }}
            </div>
        @endif
    </div>

    <!-- 2. Four Parameter Metrics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Metric 1: NDVI -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase">NDVI Terkini (Sentinel-2)</p>
            <div class="flex items-baseline space-x-2 mt-1">
                <span class="text-2xl font-bold font-mono text-emerald-700">{{ $latestObservation?->ndvi ?? '0.00' }}</span>
                <span class="text-xs font-medium text-slate-500">
                    B8: {{ $latestObservation?->b8_nir ?? '-' }} | B4: {{ $latestObservation?->b4_red ?? '-' }}
                </span>
            </div>
            <p class="text-[11px] text-slate-500 mt-1">
                Status: <strong class="text-emerald-800">{{ $latestObservation?->vegetation_status ?? 'Vegetasi Sehat' }}</strong>
            </p>
        </div>

        <!-- Metric 2: Neraca Air -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase">Neraca Air Akhir</p>
            <div class="flex items-baseline space-x-2 mt-1">
                <span class="text-2xl font-bold font-mono {{ ($latestWaterBalance?->deficit_surplus ?? 0) < 0 ? 'text-rose-600' : 'text-blue-600' }}">
                    {{ $latestWaterBalance?->deficit_surplus ?? '0.0' }} mm
                </span>
                <span class="text-xs font-semibold {{ ($latestWaterBalance?->deficit_surplus ?? 0) < 0 ? 'text-rose-500' : 'text-blue-500' }}">
                    {{ ($latestWaterBalance?->deficit_surplus ?? 0) < 0 ? 'Defisit' : 'Surplus' }}
                </span>
            </div>
            <p class="text-[11px] text-slate-500 mt-1">
                Kebutuhan ETc: <strong>{{ $latestWaterBalance?->crop_water_use ?? '-' }} mm/hari</strong>
            </p>
        </div>

        <!-- Metric 3: Hujan 7 Hari -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase">Akumulasi Hujan 7 Hari</p>
            <div class="flex items-baseline space-x-2 mt-1">
                <span class="text-2xl font-bold font-mono text-blue-700">{{ $latestDrought?->rainfall_7d ?? '0.0' }} mm</span>
            </div>
            <p class="text-[11px] text-slate-500 mt-1">
                Sumber: CHIRPS/AWS Interpolasi
            </p>
        </div>

        <!-- Metric 4: Prakiraan Hujan 3 Hari -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase">Prakiraan Hujan 3 Hari BMKG</p>
            <div class="flex items-baseline space-x-2 mt-1">
                <span class="text-2xl font-bold font-mono text-cyan-700">{{ $latestDrought?->forecast_rain_3d ?? '0.0' }} mm</span>
                <span class="text-xs text-slate-500">
                    Kebutuhan: {{ $latestDrought?->water_need_mm ?? '-' }} mm
                </span>
            </div>
            <p class="text-[11px] mt-1 font-semibold {{ ($latestDrought?->forecast_rain_3d ?? 0) >= ($latestDrought?->water_need_mm ?? 1) ? 'text-emerald-700' : 'text-rose-700' }}">
                {{ ($latestDrought?->forecast_rain_3d ?? 0) >= ($latestDrought?->water_need_mm ?? 1) ? '✓ Prakiraan Mencukupi' : '⚠ Defisit Kebutuhan Air' }}
            </p>
        </div>
    </div>

    <!-- 3. Spatial Map & Agronomic Profile -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left: Leaflet Parcel View (7 Cols) -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <h4 class="font-bold text-slate-900 text-sm">Visualisasi Spasial Polygon Parcel Lahan</h4>
                </div>
                <span class="font-mono text-xs text-slate-500">{{ $land->latitude }}, {{ $land->longitude }}</span>
            </div>
            <div id="landMap" class="w-full h-80 z-0 bg-slate-100"></div>
            <div class="p-3 bg-slate-50 border-t border-slate-100 text-xs text-slate-600 flex items-center justify-between">
                <span>Luas Spasial: <strong>{{ $land->luas }} Ha ({{ number_format($land->luas_m2, 0, ',', '.') }} m²)</strong></span>
                <span class="text-[11px] text-slate-400">Layer Basemap: OpenStreetMap Standard</span>
            </div>
        </div>

        <!-- Right: Agronomic Profile & Recommendation Card (5 Cols) -->
        <div class="lg:col-span-5 space-y-4">
            <!-- Profil Tanaman -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-3">
                <h4 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-2">Profil Agronomi & Tanah</h4>
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div>
                        <span class="text-slate-400 block">Komoditas:</span>
                        <strong class="text-slate-900">{{ $land->jenis_tanaman }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Umur Tanaman:</span>
                        <strong class="text-slate-900">{{ $land->umur_tanaman }} HST</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Tanggal Tanam:</span>
                        <span class="font-mono text-slate-700">{{ \Carbon\Carbon::parse($land->tanggal_tanam)->format('d F Y') }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Jenis Tanah:</span>
                        <strong class="text-slate-900">{{ $land->jenis_tanah }}</strong>
                    </div>
                    <div class="col-span-2">
                        <span class="text-slate-400 block">Catatan Lapangan:</span>
                        <p class="text-slate-700 italic mt-0.5">{{ $land->catatan ?? 'Tidak ada catatan khusus.' }}</p>
                    </div>
                </div>
            </div>

            <!-- Rekomendasi Alokasi Air -->
            @if ($latestRecommendation)
                <div class="bg-gradient-to-br from-indigo-50 to-blue-50 border border-indigo-200 rounded-2xl p-5 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $latestRecommendation->priority_level === 'TINGGI' ? 'bg-rose-100 text-rose-800' : 'bg-indigo-100 text-indigo-800' }}">
                            Prioritas {{ $latestRecommendation->priority_level }}
                        </span>
                        <span class="text-xs font-mono font-bold text-indigo-900">
                            Kuota: {{ $latestRecommendation->water_quota_estimate_mm }} mm
                        </span>
                    </div>
                    <h5 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Rekomendasi Tindakan Irigasi</h5>
                    <p class="text-xs text-slate-700 leading-relaxed">{{ $latestRecommendation->recommendation_text }}</p>
                    <div class="pt-2 border-t border-indigo-200/60 text-[11px] text-slate-600">
                        <strong>Alasan Ilmiah:</strong> {{ $latestRecommendation->rationale }}
                    </div>
                </div>
            @endif
        </div>

    </div>

    <!-- 4. Historical Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- 30 Days Rainfall Chart -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <h4 class="font-bold text-slate-800 text-sm mb-3">Histori Curah Hujan 30 Hari Terakhir</h4>
            <div class="h-60">
                <canvas id="landRainfallChart"></canvas>
            </div>
        </div>

        <!-- 60 Days NDVI Trend Chart -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <h4 class="font-bold text-slate-800 text-sm mb-3">Dinamika Indeks Vegetasi NDVI (Sentinel-2)</h4>
            <div class="h-60">
                <canvas id="landNdviChart"></canvas>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- 1. Inisialisasi Map Polygon Lahan ---
        const landCoords = {!! json_encode($land->polygon_geojson) !!};
        const centerLat = {{ $land->latitude }};
        const centerLng = {{ $land->longitude }};
        const statusColor = "{{ \App\Models\DroughtAnalysis::$statusColors[$status] ?? '#10b981' }}";

        const map = L.map('landMap').setView([centerLat, centerLng], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        if (landCoords) {
            const polygon = L.geoJSON(landCoords, {
                style: {
                    color: statusColor,
                    weight: 3,
                    opacity: 1,
                    fillColor: statusColor,
                    fillOpacity: 0.45
                }
            }).addTo(map);

            map.fitBounds(polygon.getBounds(), { padding: [40, 40] });
        }

        // --- 2. Chart Curah Hujan Lahan (API) ---
        fetch("{{ route('api.lands.rainfall', $land->id) }}")
            .then(res => res.json())
            .then(res => {
                const labels = res.data.map(d => d.date);
                const values = res.data.map(d => d.rainfall_mm);

                new Chart(document.getElementById('landRainfallChart'), {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Curah Hujan (mm)',
                            data: values,
                            backgroundColor: '#3b82f6',
                            borderRadius: 3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true } }
                    }
                });
            });

        // --- 3. Chart NDVI Lahan (API) ---
        fetch("{{ route('api.lands.ndvi', $land->id) }}")
            .then(res => res.json())
            .then(res => {
                const labels = res.data.map(d => d.date);
                const values = res.data.map(d => d.ndvi);

                new Chart(document.getElementById('landNdviChart'), {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'NDVI',
                            data: values,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.15)',
                            fill: true,
                            tension: 0.3,
                            pointRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { min: 0, max: 1.0 } }
                    }
                });
            });
    });
</script>
@endpush
