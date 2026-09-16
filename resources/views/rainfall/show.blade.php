@extends('layouts.app')

@section('title', 'Histori Curah Hujan - ' . $land->nama_lahan)
@section('page_title', 'Curah Hujan Lahan: ' . $land->nama_lahan)
@section('page_subtitle', 'Analisis Deret Waktu Presipitasi Harian 30 Hari Terakhir • Kec. ' . $land->kecamatan)

@section('header_actions')
<a href="{{ route('rainfall.index') }}" class="inline-flex items-center px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
    &larr; Kembali ke Ringkasan Hujan
</a>
@endsection

@section('content')
<div class="space-y-6">

    <!-- KPI Highlights -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase">Curah Hujan Hari Ini</p>
            <h3 class="text-2xl font-bold font-mono text-blue-700 mt-1">{{ number_format($today, 1) }} mm</h3>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase">Akumulasi 7 Hari Terakhir</p>
            <h3 class="text-2xl font-bold font-mono text-slate-800 mt-1">{{ number_format($sum7d, 1) }} mm</h3>
            <p class="text-[11px] text-slate-500 mt-0.5">Rerata: {{ number_format($sum7d / 7, 1) }} mm/hari</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase">Akumulasi 30 Hari</p>
            <h3 class="text-2xl font-bold font-mono text-slate-800 mt-1">{{ number_format($sum30d, 1) }} mm</h3>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase">Tren Presipitasi</p>
            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold mt-1 {{ $trend === 'KERING' ? 'bg-rose-100 text-rose-800' : 'bg-blue-100 text-blue-800' }}">
                {{ $trend }}
            </span>
        </div>
    </div>

    <!-- 30-Day Rainfall Chart -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h4 class="font-bold text-slate-800 text-sm">Fluktuasi Harian Presipitasi (mm)</h4>
                <p class="text-xs text-slate-400">Data bersumber dari interpolasi stasiun cuaca & satelit {{ $isDemo ? '(DATA DEMO)' : '(DATA RIIL)' }}</p>
            </div>
            <span class="px-2.5 py-1 rounded bg-blue-50 text-blue-700 font-mono text-xs font-bold">30 Hari</span>
        </div>
        <div class="h-64">
            <canvas id="singleRainChart"></canvas>
        </div>
    </div>

    <!-- Table of 30-day records -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100">
            <h4 class="font-bold text-slate-800 text-sm">Catatan Harian Pengamatan Presipitasi</h4>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3 px-4">Tanggal Pengamatan</th>
                        <th class="py-3 px-4">Curah Hujan (mm)</th>
                        <th class="py-3 px-4">Klasifikasi Harian</th>
                        <th class="py-3 px-4">Sumber Data</th>
                        <th class="py-3 px-4">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach ($records as $record)
                        @php
                            $val = $record->rainfall_mm;
                            $klasifikasi = $val == 0 ? 'Hari Tanpa Hujan (Kering)' : ($val < 5 ? 'Hujan Sangat Ringan' : ($val < 20 ? 'Hujan Ringan - Sedang' : 'Hujan Lebat'));
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-2.5 px-4 font-mono">
                                {{ \Carbon\Carbon::parse($record->observation_date)->translatedFormat('l, d F Y') }}
                            </td>
                            <td class="py-2.5 px-4 font-mono font-bold {{ $val > 0 ? 'text-blue-700' : 'text-slate-400' }}">
                                {{ number_format($val, 1) }} mm
                            </td>
                            <td class="py-2.5 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $val == 0 ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $klasifikasi }}
                                </span>
                            </td>
                            <td class="py-2.5 px-4 font-mono text-[11px] text-slate-500">
                                {{ $record->source }}
                            </td>
                            <td class="py-2.5 px-4 text-slate-500 text-[11px]">
                                {{ $record->notes ?? '-' }}
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
        const values = {!! json_encode($chartData->pluck('rainfall_mm')) !!};

        new Chart(document.getElementById('singleRainChart'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Curah Hujan (mm)',
                    data: values,
                    backgroundColor: '#3b82f6',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, title: { display: true, text: 'Milimeter (mm)' } }
                }
            }
        });
    });
</script>
@endpush
