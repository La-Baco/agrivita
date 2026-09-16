@extends('layouts.app')

@section('title', 'Riwayat Lahan - ' . $land->nama_lahan)
@section('page_title', 'Riwayat & Tren Lahan: ' . $land->nama_lahan)
@section('page_subtitle', 'Analisis Deret Waktu Terintegrasi (Hujan, NDVI, Neraca Air, dan Status Risiko) • Kec. ' . $land->kecamatan)

@section('header_actions')
<a href="{{ route('history.index') }}" class="inline-flex items-center px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
    &larr; Pilih Lahan Lain
</a>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Timeline Chart -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h4 class="font-bold text-slate-800 text-sm">Kurva Sinkronisasi Dinamika Spasial Lahan</h4>
                <p class="text-xs text-slate-400">Komparasi nilai NDVI Sentinel-2, Curah Hujan 7 Hari, dan Defisit Neraca Air</p>
            </div>
            <span class="px-2.5 py-1 rounded bg-slate-100 text-slate-700 font-mono text-xs font-bold">Deret Waktu Historis</span>
        </div>
        <div class="h-72">
            <canvas id="historyTimelineChart"></canvas>
        </div>
    </div>

    <!-- Chronological Analysis Records Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h4 class="font-bold text-slate-800 text-sm">Catatan Riwayat Analisis Risiko Kekeringan</h4>
            @if (config('app.data_mode') === 'DEMO')
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">DATA DEMO</span>
            @else
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">DATA RIIL</span>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3 px-4">Tanggal Analisis</th>
                        <th class="py-3 px-4">Status Risiko</th>
                        <th class="py-3 px-4">Skor Total</th>
                        <th class="py-3 px-4">Neraca Air</th>
                        <th class="py-3 px-4">Hujan 7 Hari</th>
                        <th class="py-3 px-4">Nilai NDVI</th>
                        <th class="py-3 px-4">Prakiraan 3D</th>
                        <th class="py-3 px-4">Ringkasan Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($droughtHistory as $item)
                        @php
                            $status = $item->status;
                            $metrics = $item->metrics_json ?? [];
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-4 font-mono font-medium">
                                {{ \Carbon\Carbon::parse($item->analysis_date)->translatedFormat('d F Y') }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $status === 'MERAH' ? 'bg-red-100 text-red-800' : ($status === 'ORANYE' ? 'bg-orange-100 text-orange-800' : ($status === 'KUNING' ? 'bg-yellow-100 text-yellow-800' : 'bg-emerald-100 text-emerald-800')) }}">
                                    {{ $item->status_label }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-mono font-bold text-slate-800">
                                {{ $metrics['total_score'] ?? 0 }}/100
                            </td>
                            <td class="py-3 px-4 font-mono {{ $item->water_balance_value < 0 ? 'text-rose-600 font-bold' : 'text-slate-800' }}">
                                {{ number_format($item->water_balance_value, 1) }} mm
                            </td>
                            <td class="py-3 px-4 font-mono">
                                {{ number_format($item->rainfall_7d, 1) }} mm
                            </td>
                            <td class="py-3 px-4 font-mono text-emerald-700 font-bold">
                                {{ number_format($item->ndvi_value, 3) }}
                            </td>
                            <td class="py-3 px-4 font-mono text-blue-700">
                                {{ number_format($item->forecast_rain_3d, 1) }} mm
                            </td>
                            <td class="py-3 px-4 text-slate-600 max-w-xs truncate text-[11px]" title="{{ $item->analysis_notes }}">
                                {{ $item->analysis_notes }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-6 text-center text-slate-400">Belum ada riwayat tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const labels = {!! json_encode($timelineData->pluck('date')) !!};
        const ndviValues = {!! json_encode($timelineData->pluck('ndvi')->map(fn($v) => $v * 100)) !!}; // Scale 0-100 for visual comparison
        const rain7dValues = {!! json_encode($timelineData->pluck('rainfall_7d')) !!};
        const wbValues = {!! json_encode($timelineData->pluck('water_balance')) !!};

        new Chart(document.getElementById('historyTimelineChart'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'NDVI (Dikalikan 100)',
                        data: ndviValues,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.3,
                        pointRadius: 4
                    },
                    {
                        label: 'Curah Hujan 7 Hari (mm)',
                        data: rain7dValues,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.3,
                        pointRadius: 4
                    },
                    {
                        label: 'Neraca Air Lahan (mm)',
                        data: wbValues,
                        borderColor: '#f97316',
                        backgroundColor: 'rgba(249, 115, 22, 0.1)',
                        tension: 0.3,
                        pointRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { title: { display: true, text: 'Nilai Terukur' } }
                }
            }
        });
    });
</script>
@endpush
