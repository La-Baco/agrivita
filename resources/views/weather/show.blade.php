@extends('layouts.app')

@section('title', 'Prakiraan Cuaca - ' . $land->nama_lahan)
@section('page_title', 'Prakiraan Cuaca: ' . $land->nama_lahan)
@section('page_subtitle', 'Proyeksi Parameter Meteorologi 3 Hari BMKG di Desa ' . $land->desa . ', Kec. ' . $land->kecamatan)

@section('header_actions')
<a href="{{ route('weather.index') }}" class="inline-flex items-center px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
    &larr; Kembali ke Daftar Cuaca
</a>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase">Estimasi Hujan 3 Hari</p>
            <h3 class="text-2xl font-bold font-mono text-blue-700 mt-1">{{ number_format($totalRain3d, 1) }} mm</h3>
            <p class="text-xs text-slate-500 mt-1">Akumulasi total selama 72 jam ke depan</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase">Suhu Udara Rata-rata</p>
            <h3 class="text-2xl font-bold font-mono text-amber-600 mt-1">{{ number_format($latestForecast?->temperature ?? 32, 1) }} °C</h3>
            <p class="text-xs text-slate-500 mt-1">Rentang iklim pesisir Madura</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase">Kelembapan Udara</p>
            <h3 class="text-2xl font-bold font-mono text-cyan-600 mt-1">{{ number_format($latestForecast?->humidity ?? 65, 0) }} %</h3>
            <p class="text-xs text-slate-500 mt-1">Kecepatan angin: {{ number_format($latestForecast?->wind_speed ?? 12, 0) }} km/jam</p>
        </div>
    </div>

    <!-- 3-Day Forecast Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h4 class="font-bold text-slate-800 text-sm">Rincian Prakiraan Cuaca 72 Jam BMKG</h4>
            @if ($isDemo)
                <span class="px-2.5 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-300">DATA DEMO</span>
            @else
                <span class="px-2.5 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">DATA RIIL</span>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-4">Waktu & Tanggal</th>
                        <th class="py-3.5 px-4">Kondisi Cuaca</th>
                        <th class="py-3.5 px-4">Suhu (°C)</th>
                        <th class="py-3.5 px-4">Kelembapan (%)</th>
                        <th class="py-3.5 px-4">Kecepatan Angin</th>
                        <th class="py-3.5 px-4">Estimasi Hujan (mm)</th>
                        <th class="py-3.5 px-4">Sumber Data</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach ($forecast3Days as $fc)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-4 font-mono font-medium">
                                {{ \Carbon\Carbon::parse($fc->forecast_datetime)->translatedFormat('l, d F Y - H:i') }} WIB
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-900">{{ $fc->weather }}</span>
                            </td>
                            <td class="py-3 px-4 font-mono font-semibold text-slate-800">
                                {{ number_format($fc->temperature, 1) }} °C
                            </td>
                            <td class="py-3 px-4 font-mono">
                                {{ number_format($fc->humidity, 0) }} %
                            </td>
                            <td class="py-3 px-4">
                                {{ number_format($fc->wind_speed, 1) }} km/jam ({{ $fc->wind_direction ?? 'Timur' }})
                            </td>
                            <td class="py-3 px-4 font-mono font-bold text-blue-700">
                                {{ number_format($fc->rainfall_estimate, 1) }} mm
                            </td>
                            <td class="py-3 px-4 font-mono text-[11px] text-slate-500">
                                {{ $fc->source }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
