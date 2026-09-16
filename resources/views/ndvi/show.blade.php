@extends('layouts.app')

@section('title', 'Sentinel-2 & NDVI - ' . $land->nama_lahan)
@section('page_title', 'Analisis NDVI Sentinel-2: ' . $land->nama_lahan)
@section('page_subtitle', 'Kurva Deret Waktu Revisit Satelit 60 Hari Terakhir • Resolusi Spasial 10 Meter')

@section('header_actions')
<a href="{{ route('ndvi.index') }}" class="inline-flex items-center px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
    &larr; Kembali ke Ringkasan NDVI
</a>
@endsection

@section('content')
<div class="space-y-6">

    <!-- KPI Summary Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase">Nilai NDVI Terkini</p>
            <h3 class="text-3xl font-bold font-mono text-emerald-700 mt-1">{{ number_format($ndviData['latest'], 3) }}</h3>
            <p class="text-xs text-slate-500 mt-1">Klasifikasi: <strong class="text-emerald-800">{{ $ndviData['classification'] }}</strong></p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase">Dinamika Perubahan (vs Siklus Lalu)</p>
            <div class="flex items-baseline space-x-2 mt-1">
                <span class="text-3xl font-bold font-mono {{ $ndviData['change_pct'] <= -15 ? 'text-rose-600' : 'text-slate-800' }}">
                    {{ $ndviData['change_pct'] > 0 ? '+' : '' }}{{ number_format($ndviData['change_pct'], 1) }}%
                </span>
                <span class="text-xs font-semibold {{ $ndviData['change_pct'] <= -15 ? 'text-rose-500' : 'text-slate-500' }}">
                    {{ $ndviData['change_pct'] <= -15 ? 'Indikasi Stres Klorofil' : 'Fluktuasi Normal' }}
                </span>
            </div>
            <p class="text-xs text-slate-400 mt-1">Siklus Sebelumnya: {{ number_format($ndviData['previous'], 3) }}</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase">Sensor & Platform Satelit</p>
            <h3 class="text-xl font-bold text-slate-900 mt-1">Sentinel-2 MSI (L2A)</h3>
            <p class="text-xs text-slate-500 mt-1">Tile: <span class="font-mono font-semibold">T49MEV (Sumenep)</span> • 10m Ground Resolution</p>
        </div>
    </div>

    <!-- Interactive Time-Series Chart -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h4 class="font-bold text-slate-800 text-sm">Kurva Progresi Indeks Vegetasi NDVI (60 Hari)</h4>
                <p class="text-xs text-slate-400">Nilai diekstraksi dari piksel poligon batas lahan</p>
            </div>
            <span class="px-2.5 py-1 rounded bg-emerald-50 text-emerald-700 font-mono text-xs font-bold">12 Siklus Observasi</span>
        </div>
        <div class="h-64">
            <canvas id="singleNdviChart"></canvas>
        </div>
    </div>

    <!-- Observation Records Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h4 class="font-bold text-slate-800 text-sm">Catatan Observasi Spektral Sentinel-2</h4>
            @if ($isDemo)
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">DATA DEMO</span>
            @else
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">DATA RIIL</span>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3 px-4">Tanggal Observasi</th>
                        <th class="py-3 px-4">Band 4 (Red 665nm)</th>
                        <th class="py-3 px-4">Band 8 (NIR 842nm)</th>
                        <th class="py-3 px-4">Nilai NDVI</th>
                        <th class="py-3 px-4">Tutupan Awan (%)</th>
                        <th class="py-3 px-4">Sumber Data</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach ($history as $obs)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-2.5 px-4 font-mono font-medium">
                                {{ \Carbon\Carbon::parse($obs->observation_date)->translatedFormat('d F Y') }}
                            </td>
                            <td class="py-2.5 px-4 font-mono">
                                {{ number_format($obs->b4_red, 4) }}
                            </td>
                            <td class="py-2.5 px-4 font-mono">
                                {{ number_format($obs->b8_nir, 4) }}
                            </td>
                            <td class="py-2.5 px-4 font-mono font-bold {{ $obs->ndvi < 0.35 ? 'text-rose-600' : 'text-emerald-700' }}">
                                {{ number_format($obs->ndvi, 4) }}
                            </td>
                            <td class="py-2.5 px-4 font-mono">
                                {{ number_format($obs->cloud_percentage, 1) }}%
                            </td>
                            <td class="py-2.5 px-4 font-mono text-[11px] text-slate-500">
                                {{ $obs->source }}
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
        const labels = {!! json_encode($chartData->pluck('date')) !!};
        const values = {!! json_encode($chartData->pluck('ndvi')) !!};

        new Chart(document.getElementById('singleNdviChart'), {
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
                    pointRadius: 5,
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
                        title: { display: true, text: 'Nilai NDVI (0 - 1)' }
                    }
                }
            }
        });
    });
</script>
@endpush
