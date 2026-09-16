@extends('layouts.app')

@section('title', 'Prakiraan Cuaca BMKG')
@section('page_title', 'Prakiraan Cuaca BMKG Lahan')
@section('page_subtitle', 'Proyeksi Cuaca 3 Hari ke Depan Berbasis Koordinat Titik Lahan di Kabupaten Sumenep')

@section('content')
<div class="space-y-6">

    <!-- Header Alert -->
    <div class="p-4 rounded-2xl {{ $isDemo ? 'bg-amber-50/80 border-amber-200' : 'bg-emerald-50/80 border-emerald-200' }} border flex items-start justify-between">
        <div class="flex items-start space-x-3">
            <div class="p-2 rounded-xl {{ $isDemo ? 'bg-amber-500' : 'bg-emerald-600' }} text-white mt-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <div>
                <div class="flex items-center space-x-2">
                    <h4 class="text-sm font-bold {{ $isDemo ? 'text-amber-950' : 'text-emerald-950' }}">Integrasi Data Terbuka Cuaca & BMKG</h4>
                    @if ($isDemo)
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-200 text-amber-900 border border-amber-300">DATA DEMO</span>
                    @else
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-200 text-emerald-900 border border-emerald-300">DATA RIIL</span>
                    @endif
                </div>
                <p class="text-xs {{ $isDemo ? 'text-amber-800/90' : 'text-emerald-800/90' }} mt-0.5 max-w-3xl leading-relaxed">
                    Prakiraan cuaca mencakup parameter suhu udara (°C), kelembapan relatif (%), arah dan kecepatan angin (km/jam), serta estimasi akumulasi presipitasi 3 hari ke depan. Nilai prakiraan ini digunakan untuk mengevaluasi apakah hujan mendatang mampu menutupi kebutuhan air tanaman.
                </p>
            </div>
        </div>
        <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-bold whitespace-nowrap">
            Proyeksi 3 Hari
        </span>
    </div>

    <!-- Weather Forecast Grid of Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach ($forecasts as $item)
            @php
                $land = $item['land'];
                $forecastList = $item['forecast'];
                $latest = $item['latest'];
                $rain3d = $item['rain_3d'];
            @endphp
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 flex flex-col justify-between">
                <div>
                    <div class="flex items-start justify-between border-b border-slate-100 pb-3">
                        <div>
                            <span class="font-mono text-[10px] text-slate-400">{{ $land->kode_lahan }}</span>
                            <h4 class="font-bold text-slate-900 text-sm mt-0.5">{{ $land->nama_lahan }}</h4>
                            <p class="text-xs text-slate-500">Desa {{ $land->desa }}, Kec. {{ $land->kecamatan }}</p>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-semibold text-slate-400 block">Akumulasi Hujan 3 Hari</span>
                            <span class="font-mono font-bold text-base {{ $rain3d < 5 ? 'text-rose-600' : 'text-blue-700' }}">
                                {{ number_format($rain3d, 1) }} mm
                            </span>
                        </div>
                    </div>

                    <!-- 3-Day Mini Timeline -->
                    <div class="grid grid-cols-3 gap-2 my-4">
                        @foreach ($forecastList as $idx => $fc)
                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-center">
                                <span class="text-[10px] font-bold text-slate-400 uppercase block">
                                    {{ $idx == 0 ? 'Hari Ini' : ($idx == 1 ? 'Besok' : '+2 Hari') }}
                                </span>
                                <div class="text-sm font-bold text-slate-800 my-1">
                                    {{ $fc->weather }}
                                </div>
                                <div class="text-[11px] font-semibold text-slate-600">
                                    {{ number_format($fc->temperature, 1) }}°C
                                </div>
                                <div class="text-[10px] font-mono text-blue-600 font-bold mt-1">
                                    {{ number_format($fc->rainfall_estimate, 1) }} mm
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-500">
                        Kelembapan: <strong>{{ number_format($latest?->humidity ?? 65, 0) }}%</strong> • Angin: <strong>{{ number_format($latest?->wind_speed ?? 12, 0) }} km/h</strong>
                    </span>
                    <a href="{{ route('weather.show', $land->id) }}" class="font-semibold text-brand-600 hover:text-brand-800">
                        Detail Meteorologi &rarr;
                    </a>
                </div>
            </div>
        @endforeach
    </div>

</div>
@endsection
