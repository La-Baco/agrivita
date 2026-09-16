@extends('layouts.app')

@section('title', 'Neraca Air - ' . $land->nama_lahan)
@section('page_title', 'Neraca Air Lahan: ' . $land->nama_lahan)
@section('page_subtitle', 'Buku Kas Neraca Air Harian (Kandungan Air, Hujan Efektif, ETc, dan Perkolasi) • Kec. ' . $land->kecamatan)

@section('header_actions')
<a href="{{ route('water-balance.index') }}" class="inline-flex items-center px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
    &larr; Kembali ke Neraca Air
</a>
@endsection

@section('content')
<div class="space-y-6">

    <!-- KPI Summary Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase">Neraca Air Terkini</p>
            <h3 class="text-2xl font-bold font-mono text-cyan-700 mt-1">{{ number_format($latestBalance?->final_water_balance ?? 0, 1) }} mm</h3>
            <p class="text-[11px] text-slate-500 mt-0.5">Kapasitas Lapang: 100 mm</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase">Defisit / Surplus</p>
            <h3 class="text-2xl font-bold font-mono mt-1 {{ ($latestBalance?->deficit_surplus ?? 0) < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                {{ number_format($latestBalance?->deficit_surplus ?? 0, 1) }} mm
            </h3>
            <p class="text-[11px] font-semibold {{ ($latestBalance?->deficit_surplus ?? 0) < 0 ? 'text-rose-500' : 'text-emerald-500' }} mt-0.5">
                {{ ($latestBalance?->deficit_surplus ?? 0) < 0 ? '⚠ Terjadi Defisit Air' : '✓ Cukup Terpenuhi' }}
            </p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase">Kebutuhan ETc Harian</p>
            <h3 class="text-2xl font-bold font-mono text-amber-700 mt-1">{{ number_format($latestBalance?->crop_water_use ?? 4.5, 1) }} mm/hr</h3>
            <p class="text-[11px] text-slate-500 mt-0.5">Fase: {{ $latestBalance?->details_json['growth_stage'] ?? '-' }}</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase">Koefisien Kc Tanaman</p>
            <h3 class="text-2xl font-bold font-mono text-slate-800 mt-1">{{ number_format($latestBalance?->details_json['kc'] ?? 1.0, 2) }}</h3>
            <p class="text-[11px] text-slate-500 mt-0.5">ETo Acuan: {{ $latestBalance?->details_json['et0_mm_per_day'] ?? 4.5 }} mm/hari</p>
        </div>
    </div>

    <!-- Multi-metric Chart -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h4 class="font-bold text-slate-800 text-sm">Grafik Komparasi Hujan Efektif, ETc, dan Neraca Air</h4>
                <p class="text-xs text-slate-400">Dihitung harian menggunakan parameter fisik transparan</p>
            </div>
            <span class="px-2.5 py-1 rounded bg-cyan-50 text-cyan-800 font-mono text-xs font-bold">14 Hari Terakhir</span>
        </div>
        <div class="h-64">
            <canvas id="waterBalanceChart"></canvas>
        </div>
    </div>

    <!-- Daily Water Balance History Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h4 class="font-bold text-slate-800 text-sm">Buku Kas Hidrologi Harian Lahan</h4>
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
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Air Awal</th>
                        <th class="py-3 px-4">Hujan Efektif (+)</th>
                        <th class="py-3 px-4">Irigasi Masuk (+)</th>
                        <th class="py-3 px-4">ETc Tanaman (-)</th>
                        <th class="py-3 px-4">Perkolasi (-)</th>
                        <th class="py-3 px-4">Neraca Akhir (=)</th>
                        <th class="py-3 px-4">Defisit/Surplus</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach ($history as $row)
                        @php
                            $isDeficit = $row->deficit_surplus < 0;
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-2.5 px-4 font-mono font-medium">
                                {{ \Carbon\Carbon::parse($row->calculation_date)->translatedFormat('d F Y') }}
                            </td>
                            <td class="py-2.5 px-4 font-mono">
                                {{ number_format($row->initial_water, 1) }} mm
                            </td>
                            <td class="py-2.5 px-4 font-mono font-bold text-blue-700">
                                +{{ number_format($row->effective_rainfall, 1) }} mm
                            </td>
                            <td class="py-2.5 px-4 font-mono text-cyan-700">
                                +{{ number_format($row->irrigation, 1) }} mm
                            </td>
                            <td class="py-2.5 px-4 font-mono font-bold text-amber-700">
                                -{{ number_format($row->crop_water_use, 1) }} mm
                            </td>
                            <td class="py-2.5 px-4 font-mono text-slate-400">
                                -{{ number_format($row->water_loss, 1) }} mm
                            </td>
                            <td class="py-2.5 px-4 font-mono font-bold {{ $isDeficit ? 'text-rose-600' : 'text-slate-900' }}">
                                {{ number_format($row->final_water_balance, 1) }} mm
                            </td>
                            <td class="py-2.5 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $isDeficit ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800' }}">
                                    {{ $row->deficit_surplus }} mm
                                </span>
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
        const balanceData = {!! json_encode($chartData->pluck('final_balance')) !!};
        const rainData = {!! json_encode($chartData->pluck('effective_rain')) !!};
        const etcData = {!! json_encode($chartData->pluck('etc')) !!};

        new Chart(document.getElementById('waterBalanceChart'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        type: 'line',
                        label: 'Neraca Air Akhir (mm)',
                        data: balanceData,
                        borderColor: '#0284c7',
                        backgroundColor: 'rgba(2, 132, 199, 0.1)',
                        borderWidth: 2.5,
                        fill: false,
                        tension: 0.2
                    },
                    {
                        type: 'bar',
                        label: 'Hujan Efektif (mm)',
                        data: rainData,
                        backgroundColor: '#3b82f6',
                        borderRadius: 3
                    },
                    {
                        type: 'bar',
                        label: 'Kebutuhan ETc (mm)',
                        data: etcData,
                        backgroundColor: '#f59e0b',
                        borderRadius: 3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { title: { display: true, text: 'Milimeter (mm)' } }
                }
            }
        });
    });
</script>
@endpush
