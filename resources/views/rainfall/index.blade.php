@extends('layouts.app')

@section('title', 'Pemantauan Curah Hujan')
@section('page_title', 'Pemantauan Curah Hujan Lahan')
@section('page_subtitle', 'Monitoring Presipitasi Harian, Akumulasi 7 & 30 Hari, dan Tren Kering Lahan Pertanian')

@section('content')
<div class="space-y-6">

    <!-- Information & Disclaimer Banner -->
    <div class="p-4 rounded-2xl bg-blue-50/80 border border-blue-200 flex items-start justify-between">
        <div class="flex items-start space-x-3">
            <div class="p-2 rounded-xl bg-blue-600 text-white mt-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z"/>
                </svg>
            </div>
            <div>
                <div class="flex items-center space-x-2">
                    <h4 class="text-sm font-bold text-blue-950">Metodologi Presipitasi Spasial</h4>
                    @if ($isDemo)
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">DATA DEMO</span>
                    @else
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">DATA RIIL</span>
                    @endif
                </div>
                <p class="text-xs text-blue-800/90 mt-0.5 max-w-3xl leading-relaxed">
                    Data curah hujan harian merefleksikan estimasi interpolasi <strong>CHIRPS (Climate Hazards Group InfraRed Precipitation with Station data)</strong> dan sensor otomatis AWS (Automatic Weather Station) di Kabupaten Sumenep. Hari tanpa hujan beruntun (dry spell) menjadi indikator primer stres hidrologis tanah.
                </p>
            </div>
        </div>
        <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-800 text-xs font-bold whitespace-nowrap">
            10 Titik Terpantau
        </span>
    </div>

    <!-- Rainfall Table by Land -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h4 class="font-bold text-slate-800 text-sm">Rekapitulasi Curah Hujan per Lahan</h4>
            <span class="text-xs text-slate-400">Satuan: Milimeter (mm)</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-4">Lahan & Lokasi</th>
                        <th class="py-3.5 px-4">Hari Ini</th>
                        <th class="py-3.5 px-4">Akumulasi 7 Hari</th>
                        <th class="py-3.5 px-4">Akumulasi 30 Hari</th>
                        <th class="py-3.5 px-4">Kondisi / Tren</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach ($landSummaries as $summary)
                        @php
                            $land = $summary['land'];
                            $trend = $summary['trend'];
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-4">
                                <a href="{{ route('rainfall.show', $land->id) }}" class="font-bold text-slate-900 hover:text-brand-600 transition">
                                    {{ $land->nama_lahan }}
                                </a>
                                <div class="text-[11px] text-slate-400">Kec. {{ $land->kecamatan }}, Desa {{ $land->desa }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-mono font-bold text-sm {{ $summary['today'] > 0 ? 'text-blue-700' : 'text-slate-400' }}">
                                    {{ number_format($summary['today'], 1) }} mm
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-mono font-bold text-sm {{ $summary['sum_7d'] < 5 ? 'text-rose-600' : 'text-slate-800' }}">
                                    {{ number_format($summary['sum_7d'], 1) }} mm
                                </span>
                                <div class="text-[10px] text-slate-400">Rerata: {{ number_format($summary['sum_7d'] / 7, 1) }} mm/hr</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-mono font-bold text-sm text-slate-800">
                                    {{ number_format($summary['sum_30d'], 1) }} mm
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold {{ $trend === 'KERING' ? 'bg-rose-100 text-rose-800' : ($trend === 'MENURUN' ? 'bg-amber-100 text-amber-800' : ($trend === 'MENINGKAT' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700')) }}">
                                    {{ $trend === 'KERING' ? '☀ Kering Kritis' : ($trend === 'MENURUN' ? '↓ Menurun' : ($trend === 'MENINGKAT' ? '↑ Meningkat' : '→ Stabil')) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('rainfall.show', $land->id) }}" class="px-3 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold transition">
                                    Grafik Harian &rarr;
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
