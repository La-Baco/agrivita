@extends('layouts.app')

@section('title', 'Peta Monitoring Spasial GIS')
@section('page_title', 'Peta Monitoring Spasial Lahan')
@section('page_subtitle', 'Sistem Informasi Geografis (GIS) Sebaran Lahan, Status Kekeringan, dan Neraca Air di Kabupaten Sumenep')

@section('header_actions')
<div class="flex items-center space-x-2">
    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-semibold border border-emerald-200">
        <span class="w-2 h-2 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
        Layer Spasial Aktif
    </span>
</div>
@endsection

@section('content')
<div class="flex flex-col h-[calc(100vh-10.5rem)] space-y-4">

    <!-- Top Filter Bar -->
    <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-sm flex flex-wrap items-center justify-between gap-3 text-xs">
        <div class="flex flex-wrap items-center gap-3">
            <span class="font-bold text-slate-700 flex items-center">
                <svg class="w-4 h-4 mr-1 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                Filter Spasial:
            </span>

            <!-- Filter Kecamatan -->
            <select id="filterKecamatan" class="px-3 py-1.5 rounded-xl border border-slate-300 bg-slate-50 text-slate-700 focus:outline-none focus:border-brand-500 font-medium">
                <option value="ALL">Semua Kecamatan</option>
                @foreach ($kecamatanList as $kec)
                    <option value="{{ $kec }}">{{ $kec }}</option>
                @endforeach
            </select>

            <!-- Filter Komoditas -->
            <select id="filterTanaman" class="px-3 py-1.5 rounded-xl border border-slate-300 bg-slate-50 text-slate-700 focus:outline-none focus:border-brand-500 font-medium">
                <option value="ALL">Semua Komoditas</option>
                @foreach ($jenisTanamanList as $tanaman)
                    <option value="{{ $tanaman }}">{{ $tanaman }}</option>
                @endforeach
            </select>

            <!-- Filter Status Kekeringan -->
            <select id="filterStatus" class="px-3 py-1.5 rounded-xl border border-slate-300 bg-slate-50 text-slate-700 focus:outline-none focus:border-brand-500 font-medium">
                <option value="ALL">Semua Status Risiko</option>
                <option value="MERAH">🔴 Kritis (Prioritas Air)</option>
                <option value="ORANYE">🟠 Waspada (Risiko Kekeringan)</option>
                <option value="KUNING">🟡 Perhatian (Defisit Ringan)</option>
                <option value="HIJAU">🟢 Aman (Air Cukup)</option>
            </select>
        </div>

        <!-- Basemap Toggles -->
        <div class="flex items-center space-x-2">
            <span class="text-slate-400 text-[11px] uppercase font-semibold">Basemap:</span>
            <button type="button" id="btnOsm" class="px-2.5 py-1 rounded-lg bg-slate-900 text-white font-semibold text-xs transition">
                Street Map
            </button>
            <button type="button" id="btnSat" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                Citra Satelit
            </button>
        </div>
    </div>

    <!-- GIS Map View & Side Inspector Workstation -->
    <div class="flex-1 grid grid-cols-1 lg:grid-cols-12 gap-4 min-h-0">

        <!-- Map Container (9 Cols) -->
        <div class="lg:col-span-9 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden relative flex flex-col">
            <div id="gisMap" class="w-full h-full min-h-[450px] z-0"></div>

            <!-- Floating Legend -->
            <div class="absolute bottom-4 left-4 z-[500] bg-white/95 backdrop-blur-md p-3.5 rounded-xl border border-slate-200 shadow-lg text-xs max-w-xs pointer-events-auto">
                <p class="font-bold text-slate-900 mb-2 flex items-center justify-between">
                    <span>Legenda Status Spasial</span>
                    <span class="text-[10px] text-slate-400 font-normal">4 Tingkat</span>
                </p>
                <div class="space-y-1.5">
                    <div class="flex items-center space-x-2">
                        <span class="w-4 h-3 rounded bg-red-500 border border-red-700"></span>
                        <span class="text-slate-700 font-medium">MERAH: Prioritas Air / Kritis</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="w-4 h-3 rounded bg-orange-500 border border-orange-700"></span>
                        <span class="text-slate-700 font-medium">ORANYE: Risiko Kekeringan</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="w-4 h-3 rounded bg-yellow-400 border border-yellow-600"></span>
                        <span class="text-slate-700 font-medium">KUNING: Defisit Ringan</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="w-4 h-3 rounded bg-emerald-500 border border-emerald-700"></span>
                        <span class="text-slate-700 font-medium">HIJAU: Air Cukup / Aman</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Side Inspector Panel (3 Cols) -->
        <div class="lg:col-span-3 bg-white rounded-2xl border border-slate-200 shadow-sm p-4 flex flex-col justify-between overflow-y-auto">
            <div>
                <div class="pb-3 border-b border-slate-100 flex items-center justify-between">
                    <h4 class="font-bold text-slate-900 text-xs uppercase tracking-wider">Inspektur Spasial</h4>
                    <span id="inspectBadge" class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">
                        Siap Memantau
                    </span>
                </div>

                <div id="inspectorContent" class="mt-4 space-y-4 text-xs">
                    <!-- Default State -->
                    <div id="inspectPlaceholder" class="py-12 text-center text-slate-400">
                        <svg class="w-10 h-10 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/>
                        </svg>
                        <p class="font-medium text-slate-600">Pilih salah satu poligon lahan pada peta</p>
                        <p class="text-[11px] text-slate-400 mt-1">Data parameter cuaca, satelit, dan neraca air akan dimuat secara instan.</p>
                    </div>

                    <!-- Dynamic Details (Hidden by Default) -->
                    <div id="inspectDetails" class="hidden space-y-3.5">
                        <div>
                            <span class="font-mono text-[10px] text-slate-400" id="detailKode">-</span>
                            <h3 class="font-bold text-slate-900 text-sm mt-0.5" id="detailNama">-</h3>
                            <p class="text-slate-500 text-xs" id="detailWilayah">-</p>
                        </div>

                        <div class="grid grid-cols-2 gap-2 p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                            <div>
                                <span class="text-slate-400 block text-[10px]">Komoditas:</span>
                                <strong class="text-slate-800" id="detailTanaman">-</strong>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[10px]">Umur Tanaman:</span>
                                <strong class="text-slate-800" id="detailUmur">-</strong>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[10px]">Luas Lahan:</span>
                                <strong class="text-slate-800" id="detailLuas">-</strong>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[10px]">NDVI Satelit:</span>
                                <strong class="text-emerald-700 font-mono" id="detailNdvi">-</strong>
                            </div>
                        </div>

                        <div class="p-2.5 rounded-xl border border-slate-100 space-y-1">
                            <span class="text-slate-400 block text-[10px] uppercase font-semibold">Neraca Air Terkini:</span>
                            <div class="text-sm font-bold font-mono" id="detailNeraca">-</div>
                        </div>

                        <div class="pt-2">
                            <a href="#" id="detailUrl" class="w-full py-2.5 px-3 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-center block text-xs shadow-sm transition">
                                Buka Dossier Lahan Penuh &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 text-[10px] text-slate-400 text-center">
                Koordinat EPSG:4326 WGS84 • Sentinel-2 MSI
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- 1. Setup Peta & Basemaps ---
        const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '&copy; OpenStreetMap'
        });

        const satLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 18,
            attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
        });

        const map = L.map('gisMap', {
            center: [-7.016, 113.864],
            zoom: 11,
            layers: [osmLayer]
        });

        document.getElementById('btnOsm').addEventListener('click', function() {
            map.removeLayer(satLayer);
            map.addLayer(osmLayer);
            this.className = "px-2.5 py-1 rounded-lg bg-slate-900 text-white font-semibold text-xs transition";
            document.getElementById('btnSat').className = "px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition";
        });

        document.getElementById('btnSat').addEventListener('click', function() {
            map.removeLayer(osmLayer);
            map.addLayer(satLayer);
            this.className = "px-2.5 py-1 rounded-lg bg-slate-900 text-white font-semibold text-xs transition";
            document.getElementById('btnOsm').className = "px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition";
        });

        // --- 2. Load GeoJSON & Interactive Inspector ---
        let allFeatures = [];
        let geoJsonLayer = null;

        function loadGeoJson() {
            fetch("{{ route('api.lands.index') }}")
                .then(r => r.json())
                .then(data => {
                    allFeatures = data.features;
                    renderFilteredLayer();
                });
        }

        function renderFilteredLayer() {
            if (geoJsonLayer) {
                map.removeLayer(geoJsonLayer);
            }

            const kecVal = document.getElementById('filterKecamatan').value;
            const tanamanVal = document.getElementById('filterTanaman').value;
            const statusVal = document.getElementById('filterStatus').value;

            const filtered = allFeatures.filter(f => {
                const p = f.properties;
                const matchKec = (kecVal === 'ALL' || p.kecamatan === kecVal);
                const matchTanaman = (tanamanVal === 'ALL' || p.jenis_tanaman === tanamanVal);
                const matchStatus = (statusVal === 'ALL' || p.drought_status === statusVal);
                return matchKec && matchTanaman && matchStatus;
            });

            geoJsonLayer = L.geoJSON({ type: "FeatureCollection", features: filtered }, {
                style: function(f) {
                    return {
                        color: f.properties.drought_color || '#10b981',
                        weight: 2.5,
                        opacity: 0.95,
                        fillColor: f.properties.drought_color || '#10b981',
                        fillOpacity: 0.5
                    };
                },
                onEachFeature: function(feature, layer) {
                    const p = feature.properties;

                    // Hover effects
                    layer.on('mouseover', function() {
                        this.setStyle({ weight: 4, fillOpacity: 0.75 });
                    });
                    layer.on('mouseout', function() {
                        geoJsonLayer.resetStyle(this);
                    });

                    // Click Inspector event
                    layer.on('click', function(e) {
                        inspectLand(p);
                        L.DomEvent.stopPropagation(e);
                    });

                    // Popup
                    const popupContent = `
                        <div class="p-1">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold" style="background-color: ${p.drought_color}25; color: ${p.drought_color}">
                                ${p.drought_label}
                            </span>
                            <h5 class="font-bold text-slate-900 text-sm mt-1">${p.nama_lahan}</h5>
                            <p class="text-xs text-slate-500">${p.desa}, Kec. ${p.kecamatan}</p>
                            <div class="mt-2 text-xs space-y-0.5">
                                <div>Tanaman: <strong>${p.jenis_tanaman}</strong> (${p.umur_tanaman} HST)</div>
                                <div>Luas: <strong>${p.luas} Ha</strong></div>
                                <div>NDVI: <strong>${p.ndvi ?? '-'}</strong></div>
                            </div>
                            <div class="mt-2 pt-2 border-t border-slate-200">
                                <a href="${p.url}" class="text-xs font-bold text-emerald-600 hover:underline">Buka Dossier &rarr;</a>
                            </div>
                        </div>
                    `;
                    layer.bindPopup(popupContent);
                }
            }).addTo(map);

            if (filtered.length > 0) {
                map.fitBounds(geoJsonLayer.getBounds(), { padding: [30, 30] });
            }
        }

        function inspectLand(p) {
            document.getElementById('inspectPlaceholder').classList.add('hidden');
            document.getElementById('inspectDetails').classList.remove('hidden');

            document.getElementById('detailKode').textContent = p.kode_lahan;
            document.getElementById('detailNama').textContent = p.nama_lahan;
            document.getElementById('detailWilayah').textContent = `${p.desa}, Kec. ${p.kecamatan}`;
            document.getElementById('detailTanaman').textContent = p.jenis_tanaman;
            document.getElementById('detailUmur').textContent = `${p.umur_tanaman} HST`;
            document.getElementById('detailLuas').textContent = `${p.luas} Ha`;
            document.getElementById('detailNdvi').textContent = p.ndvi ?? '-';
            document.getElementById('detailNeraca').textContent = `${p.water_balance ?? '0.0'} mm`;
            document.getElementById('detailUrl').href = p.url;

            const badge = document.getElementById('inspectBadge');
            badge.textContent = p.drought_label;
            badge.style.backgroundColor = `${p.drought_color}25`;
            badge.style.color = p.drought_color;
        }

        // Filter event listeners
        document.getElementById('filterKecamatan').addEventListener('change', renderFilteredLayer);
        document.getElementById('filterTanaman').addEventListener('change', renderFilteredLayer);
        document.getElementById('filterStatus').addEventListener('change', renderFilteredLayer);

        loadGeoJson();
    });
</script>
@endpush
