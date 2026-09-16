@extends('layouts.app')

@section('title', 'Tambah Lahan Pertanian Baru')
@section('page_title', 'Tambah Lahan Pertanian')
@section('page_subtitle', 'Gambar polygon GeoJSON dan tentukan parameter agronomis lahan di Kabupaten Sumenep')

@section('header_actions')
<a href="{{ route('lands.index') }}" class="inline-flex items-center px-3.5 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-semibold transition">
    &larr; Kembali ke Daftar Lahan
</a>
@endsection

@section('content')
<form action="{{ route('lands.store') }}" method="POST" id="landForm" class="space-y-6">
    @csrf

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left Column: Map & GeoJSON Input (7 Cols) -->
        <div class="lg:col-span-7 space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
                    <div>
                        <h4 class="font-bold text-slate-900 text-sm">Digitasi Polygon Spasial Lahan</h4>
                        <p class="text-[11px] text-slate-500">Klik pada peta minimal 3 titik untuk membentuk polygon lahan tertutup.</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button type="button" id="btnSamplePolygon" class="px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold text-xs border border-emerald-300 transition">
                            📍 Contoh Titik Sumenep
                        </button>
                        <button type="button" id="btnClearPolygon" class="px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold text-xs border border-rose-300 transition">
                            🔄 Reset
                        </button>
                    </div>
                </div>

                <!-- Leaflet Drawing Canvas -->
                <div id="drawMap" class="w-full h-96 z-0 bg-slate-100"></div>

                <div class="p-3 bg-slate-50 border-t border-slate-100 text-xs text-slate-600 flex items-center justify-between">
                    <div>Titik terplot: <strong id="pointCount">0</strong> titik</div>
                    <div class="font-mono text-[11px] text-slate-500">Centroid: <span id="centroidDisplay">-</span></div>
                </div>
            </div>

            <!-- GeoJSON Raw Output (Hidden or Collapsible) -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
                <label for="polygon_geojson" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Data GeoJSON Polygon (Terisi Otomatis)
                </label>
                <textarea id="polygon_geojson" name="polygon_geojson" rows="4" readonly required
                          class="w-full font-mono text-xs p-3 rounded-xl bg-slate-900 text-emerald-400 border border-slate-700 focus:outline-none focus:ring-1 focus:ring-brand-500">{{ old('polygon_geojson') }}</textarea>
                @error('polygon_geojson')
                    <p class="text-rose-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Right Column: Agronomic Data Inputs (5 Cols) -->
        <div class="lg:col-span-5 space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-4">
                <h4 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-2">Informasi Identitas & Agronomi</h4>

                <!-- Kode Lahan & Status -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="kode_lahan" class="block text-xs font-semibold text-slate-700 mb-1">Kode Lahan *</label>
                        <input type="text" id="kode_lahan" name="kode_lahan" value="{{ old('kode_lahan', 'LHK-SMP-' . str_pad(mt_rand(11, 99), 3, '0', STR_PAD_LEFT)) }}" required
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:border-brand-500 font-mono">
                        @error('kode_lahan') <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="status" class="block text-xs font-semibold text-slate-700 mb-1">Status Budidaya *</label>
                        <select id="status" name="status" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:border-brand-500">
                            <option value="Aktif" {{ old('status') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="Bera" {{ old('status') == 'Bera' ? 'selected' : '' }}>Bera (Istirahat)</option>
                            <option value="Panen" {{ old('status') == 'Panen' ? 'selected' : '' }}>Panen</option>
                        </select>
                    </div>
                </div>

                <!-- Nama Lahan -->
                <div>
                    <label for="nama_lahan" class="block text-xs font-semibold text-slate-700 mb-1">Nama Lahan *</label>
                    <input type="text" id="nama_lahan" name="nama_lahan" value="{{ old('nama_lahan') }}" placeholder="Contoh: Lahan Padi Ciherang Blok A" required
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:border-brand-500">
                    @error('nama_lahan') <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Wilayah Administratif -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="kecamatan" class="block text-xs font-semibold text-slate-700 mb-1">Kecamatan *</label>
                        <select id="kecamatan" name="kecamatan" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:border-brand-500">
                            <option value="">Pilih Kecamatan</option>
                            @foreach ($kecamatanOptions as $kec)
                                <option value="{{ $kec }}" {{ old('kecamatan') == $kec ? 'selected' : '' }}>{{ $kec }}</option>
                            @endforeach
                        </select>
                        @error('kecamatan') <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="desa" class="block text-xs font-semibold text-slate-700 mb-1">Desa *</label>
                        <input type="text" id="desa" name="desa" value="{{ old('desa') }}" placeholder="Nama Desa" required
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:border-brand-500">
                        @error('desa') <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <input type="hidden" name="kabupaten" value="Sumenep">

                <!-- Jenis Tanaman & Tanggal Tanam -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="jenis_tanaman" class="block text-xs font-semibold text-slate-700 mb-1">Komoditas Tanaman *</label>
                        <select id="jenis_tanaman" name="jenis_tanaman" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:border-brand-500">
                            <option value="">Pilih Tanaman</option>
                            @foreach ($jenisTanaman as $tanaman)
                                <option value="{{ $tanaman }}" {{ old('jenis_tanaman') == $tanaman ? 'selected' : '' }}>{{ $tanaman }}</option>
                            @endforeach
                        </select>
                        @error('jenis_tanaman') <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="tanggal_tanam" class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Tanam *</label>
                        <input type="date" id="tanggal_tanam" name="tanggal_tanam" value="{{ old('tanggal_tanam', now()->subDays(45)->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}" required
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:border-brand-500">
                        @error('tanggal_tanam') <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Jenis Tanah -->
                <div>
                    <label for="jenis_tanah" class="block text-xs font-semibold text-slate-700 mb-1">Jenis Tanah *</label>
                    <select id="jenis_tanah" name="jenis_tanah" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:border-brand-500">
                        <option value="">Pilih Jenis Tanah</option>
                        @foreach ($jenisTanah as $tanah)
                            <option value="{{ $tanah }}" {{ old('jenis_tanah') == $tanah ? 'selected' : '' }}>{{ $tanah }}</option>
                        @endforeach
                    </select>
                    @error('jenis_tanah') <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Luas & Centroid -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="luas" class="block text-xs font-semibold text-slate-700 mb-1">Luas (Hektar) *</label>
                        <input type="number" step="0.01" min="0.01" id="luas" name="luas" value="{{ old('luas', '1.00') }}" required
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:border-brand-500 font-mono">
                        @error('luas') <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="luas_m2" class="block text-xs font-semibold text-slate-700 mb-1">Luas (m²)</label>
                        <input type="number" id="luas_m2" name="luas_m2" value="{{ old('luas_m2', '10000') }}" readonly
                               class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-300 text-slate-600 font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="latitude" class="block text-xs font-semibold text-slate-700 mb-1">Latitude Centroid *</label>
                        <input type="number" step="any" id="latitude" name="latitude" value="{{ old('latitude', '-7.016') }}" required
                               class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-300 text-slate-600 font-mono">
                    </div>
                    <div>
                        <label for="longitude" class="block text-xs font-semibold text-slate-700 mb-1">Longitude Centroid *</label>
                        <input type="number" step="any" id="longitude" name="longitude" value="{{ old('longitude', '113.864') }}" required
                               class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-300 text-slate-600 font-mono">
                    </div>
                </div>

                <!-- Catatan -->
                <div>
                    <label for="catatan" class="block text-xs font-semibold text-slate-700 mb-1">Catatan Lapangan (Opsional)</label>
                    <textarea id="catatan" name="catatan" rows="2" placeholder="Catatan kondisi topografi, saluran irigasi, atau riwayat benih..."
                              class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:border-brand-500">{{ old('catatan') }}</textarea>
                </div>

                <button type="submit" class="w-full py-3 px-4 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-md shadow-brand-900/20 transition flex items-center justify-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan & Daftarkan Lahan
                </button>
            </div>
        </div>

    </div>
</form>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const map = L.map('drawMap').setView([-7.016, 113.864], 11);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        let vertices = [];
        let markers = [];
        let polygonLayer = null;

        const geojsonInput = document.getElementById('polygon_geojson');
        const pointCount = document.getElementById('pointCount');
        const centroidDisplay = document.getElementById('centroidDisplay');
        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');
        const luasInput = document.getElementById('luas');
        const luasM2Input = document.getElementById('luas_m2');

        function updatePolygon() {
            if (polygonLayer) {
                map.removeLayer(polygonLayer);
                polygonLayer = null;
            }

            pointCount.textContent = vertices.length;

            if (vertices.length >= 3) {
                // Polygon ring must close
                const ring = [...vertices, vertices[0]];
                const geojson = {
                    type: "Polygon",
                    coordinates: [ring.map(v => [v.lng, v.lat])]
                };

                polygonLayer = L.polygon(vertices.map(v => [v.lat, v.lng]), {
                    color: '#16a34a',
                    fillColor: '#22c55e',
                    fillOpacity: 0.4
                }).addTo(map);

                geojsonInput.value = JSON.stringify(geojson, null, 2);

                // Hitung centroid sederhana
                const avgLat = vertices.reduce((sum, v) => sum + v.lat, 0) / vertices.length;
                const avgLng = vertices.reduce((sum, v) => sum + v.lng, 0) / vertices.length;

                latInput.value = avgLat.toFixed(6);
                lngInput.value = avgLng.toFixed(6);
                centroidDisplay.textContent = `${avgLat.toFixed(4)}, ${avgLng.toFixed(4)}`;

                // Estimasi luas planar kasar
                calculateApproxArea(vertices);
            } else {
                geojsonInput.value = '';
                centroidDisplay.textContent = '-';
            }
        }

        function calculateApproxArea(points) {
            // Shoelace formula planar estimasi sederhana di lintang -7
            let area = 0;
            const n = points.length;
            for (let i = 0; i < n; i++) {
                const j = (i + 1) % n;
                const xi = points[i].lng * 111320 * Math.cos(points[i].lat * Math.PI / 180);
                const yi = points[i].lat * 110540;
                const xj = points[j].lng * 111320 * Math.cos(points[j].lat * Math.PI / 180);
                const yj = points[j].lat * 110540;
                area += (xi * yj) - (xj * yi);
            }
            const m2 = Math.abs(area / 2);
            const ha = m2 / 10000;
            if (ha > 0.01) {
                luasM2Input.value = Math.round(m2);
                luasInput.value = ha.toFixed(2);
            }
        }

        map.on('click', function(e) {
            const marker = L.circleMarker(e.latlng, {
                radius: 5,
                color: '#15803d',
                fillColor: '#86efac',
                fillOpacity: 1
            }).addTo(map);

            markers.push(marker);
            vertices.push(e.latlng);
            updatePolygon();
        });

        document.getElementById('btnClearPolygon').addEventListener('click', function() {
            markers.forEach(m => map.removeLayer(m));
            markers = [];
            vertices = [];
            if (polygonLayer) {
                map.removeLayer(polygonLayer);
                polygonLayer = null;
            }
            updatePolygon();
        });

        document.getElementById('btnSamplePolygon').addEventListener('click', function() {
            // Contoh polygon lahan di Lenteng / Sumenep
            document.getElementById('btnClearPolygon').click();

            const sampleCoords = [
                [-7.0372, 113.7812],
                [-7.0370, 113.7828],
                [-7.0388, 113.7830],
                [-7.0390, 113.7814]
            ];

            sampleCoords.forEach(c => {
                const latlng = L.latLng(c[0], c[1]);
                const marker = L.circleMarker(latlng, {
                    radius: 5,
                    color: '#15803d',
                    fillColor: '#86efac',
                    fillOpacity: 1
                }).addTo(map);
                markers.push(marker);
                vertices.push(latlng);
            });

            updatePolygon();
            map.setView([-7.0380, 113.7821], 15);
        });

        // Update m2 jika ha diubah manual
        luasInput.addEventListener('input', function() {
            const ha = parseFloat(this.value) || 0;
            luasM2Input.value = Math.round(ha * 10000);
        });
    });
</script>
@endpush
