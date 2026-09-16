@extends('layouts.app')

@section('title', 'Satelit Sentinel-2 & NDVI')
@section('page_title', 'Indeks Vegetasi NDVI (Sentinel-2)')
@section('page_subtitle', 'Analisis Reflektansi Spektral Spektrum Merah (B4) dan Inframerah Dekat (B8) Resolusi 10 Meter')

@section('content')
<div class="space-y-6">

    <!-- Formula & Sentinel-2 Information Card -->
    <div class="p-5 rounded-2xl bg-gradient-to-r from-emerald-50 via-teal-50 to-emerald-50 border border-emerald-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1.5">
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-0.5 rounded-lg bg-emerald-600 text-white font-bold text-xs">Copernicus Sentinel-2 L2A</span>
                @if ($isDemo)
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">DATA DEMO</span>
                @else
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">DATA RIIL</span>
                @endif
            </div>
            <h4 class="font-bold text-slate-900 text-sm">Rumus Normalized Difference Vegetation Index (NDVI) Terbuka:</h4>
            <div class="p-2.5 rounded-xl bg-white border border-emerald-300 font-mono text-xs text-emerald-900 inline-block font-bold shadow-sm">
                NDVI = (Band 8 NIR - Band 4 Red) / (Band 8 NIR + Band 4 Red)
            </div>
            <p class="text-xs text-slate-600 leading-relaxed max-w-2xl">
                Klorofil aktif menyerap spektrum merah (Band 4: 665 nm) dan memantulkan kuat spektrum inframerah dekat (Band 8: 842 nm). Penurunan nilai NDVI &gt; 15% mengindikasikan klorofil rusak atau stres air vegetasi secara obyektif tanpa AI.
            </p>
        </div>

        <div class="p-4 rounded-xl bg-white/80 border border-emerald-200 text-xs space-y-1 self-start md:self-auto min-w-[220px]">
            <span class="font-bold text-slate-700 block uppercase text-[10px]">Skala Klasifikasi NDVI:</span>
            <div class="text-emerald-900 font-medium">&gt; 0.60 : Kanopi Sangat Rapat / Subur</div>
            <div class="text-emerald-700 font-medium">0.40 - 0.60 : Vegetasi Rapat</div>
            <div class="text-amber-700 font-medium">0.20 - 0.40 : Vegetasi Sedang / Stres</div>
            <div class="text-rose-700 font-medium">&lt; 0.20 : Lahan Terbuka / Kering</div>
        </div>
    </div>

    <!-- NDVI Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h4 class="font-bold text-slate-800 text-sm">Hasil Ekstraksi Spektral Terkini per Lahan</h4>
            <span class="text-xs text-slate-400">Revisit Sentinel-2: 5 Hari Sekali</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-4">Lahan & Komoditas</th>
                        <th class="py-3.5 px-4">NDVI Terkini</th>
                        <th class="py-3.5 px-4">NDVI Siklus Lalu</th>
                        <th class="py-3.5 px-4">Dinamika Perubahan</th>
                        <th class="py-3.5 px-4">Status Kesehatan Kanopi</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach ($lands as $item)
                        @php
                            $land = $item['land'];
                            $change = $item['change_pct'];
                            $isDeclining = $change <= -15;
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-4">
                                <a href="{{ route('ndvi.show', $land->id) }}" class="font-bold text-slate-900 hover:text-brand-600 transition">
                                    {{ $land->nama_lahan }}
                                </a>
                                <div class="text-[11px] text-slate-400">Komoditas: <strong class="text-emerald-800">{{ $land->jenis_tanaman }}</strong> ({{ $land->umur_tanaman }} HST)</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-mono font-bold text-base {{ $item['latest_ndvi'] < 0.35 ? 'text-rose-600' : 'text-emerald-700' }}">
                                    {{ number_format($item['latest_ndvi'], 3) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-600">
                                {{ number_format($item['previous_ndvi'], 3) }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold {{ $isDeclining ? 'bg-rose-100 text-rose-800' : ($change < 0 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800') }}">
                                    {{ $change > 0 ? '+' : '' }}{{ number_format($change, 1) }}%
                                    @if ($isDeclining)
                                        <span class="ml-1 text-[10px]">⚠ Stres</span>
                                    @endif
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ $item['latest_ndvi'] >= 0.55 ? 'bg-emerald-50 text-emerald-800' : ($item['latest_ndvi'] >= 0.35 ? 'bg-yellow-50 text-yellow-800' : 'bg-rose-50 text-rose-800') }}">
                                    {{ $item['classification'] }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('ndvi.show', $land->id) }}" class="px-3 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold transition">
                                    Kurva NDVI &rarr;
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
