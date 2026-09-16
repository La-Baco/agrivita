@extends('layouts.app')

@section('title', 'Neraca Air Lahan')
@section('page_title', 'Neraca Air Lahan Pertanian')
@section('page_subtitle', 'Analisis Keseimbangan Air Berbasis Rumus Fisika Tanah dan Evapotranspirasi Tanaman (ETc = ETo × Kc)')

@section('content')
<div class="space-y-6">

    <!-- Formula Documentation Card -->
    <div class="p-5 rounded-2xl bg-cyan-50/80 border border-cyan-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1.5">
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-0.5 rounded-lg bg-cyan-700 text-white font-bold text-xs">Model Hidrologi Lahan Terbuka</span>
                @if (config('app.data_mode') === 'DEMO')
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">DATA DEMO</span>
                @else
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">DATA RIIL</span>
                @endif
            </div>
            <h4 class="font-bold text-slate-900 text-sm">Persamaan Neraca Air Harian:</h4>
            <div class="p-2.5 rounded-xl bg-white border border-cyan-300 font-mono text-xs text-cyan-900 inline-block font-bold shadow-sm">
                Neraca Akhir = Kandungan Air Awal + Hujan Efektif + Irigasi - ETc - Perkolasi
            </div>
            <p class="text-xs text-slate-600 leading-relaxed max-w-2xl">
                Evapotranspirasi tanaman <strong>(ETc = ETo × Kc)</strong> dihitung berdasarkan umur tanam spesifik dan koefisien tanaman FAO 56. Hujan efektif diestimasi 75% (USDA SCS) dan perkolasi disesuaikan dengan jenis tanah Sumenep.
            </p>
        </div>

        <div class="p-4 rounded-xl bg-white/80 border border-cyan-200 text-xs space-y-1 min-w-[220px]">
            <span class="font-bold text-slate-700 block uppercase text-[10px]">Parameter Acuan Riset:</span>
            <div>ETo Acuan: <strong>4.5 mm/hari</strong></div>
            <div>Faktor Hujan Efektif: <strong>75%</strong></div>
            <div>Laju Perkolasi: <strong>2.0 mm/hari</strong></div>
            <div>Kapasitas Lapang: <strong>100 mm</strong></div>
        </div>
    </div>

    <!-- Table of Water Balances per Land -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h4 class="font-bold text-slate-800 text-sm">Kondisi Neraca Air Terkini per Lahan</h4>
            <span class="text-xs text-slate-400">Satuan: Milimeter (mm)</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-4">Lahan & Komoditas</th>
                        <th class="py-3.5 px-4">Air Awal</th>
                        <th class="py-3.5 px-4">Hujan Efektif</th>
                        <th class="py-3.5 px-4">Kebutuhan ETc</th>
                        <th class="py-3.5 px-4">Kehilangan</th>
                        <th class="py-3.5 px-4">Neraca Akhir</th>
                        <th class="py-3.5 px-4">Status Keseimbangan</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach ($lands as $land)
                        @php
                            $wb = $land->waterBalances->first();
                            $isDeficit = ($wb?->deficit_surplus ?? 0) < 0;
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-4">
                                <a href="{{ route('water-balance.show', $land->id) }}" class="font-bold text-slate-900 hover:text-brand-600 transition">
                                    {{ $land->nama_lahan }}
                                </a>
                                <div class="text-[11px] text-slate-400">{{ $land->jenis_tanaman }} ({{ $land->umur_tanaman }} HST)</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono">
                                {{ number_format($wb?->initial_water ?? 50, 1) }} mm
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-blue-700">
                                +{{ number_format($wb?->effective_rainfall ?? 0, 1) }} mm
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-amber-700">
                                -{{ number_format($wb?->crop_water_use ?? 4.5, 1) }} mm
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-500">
                                -{{ number_format($wb?->water_loss ?? 2.0, 1) }} mm
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-sm {{ $isDeficit ? 'text-rose-600' : 'text-emerald-700' }}">
                                {{ number_format($wb?->final_water_balance ?? 0, 1) }} mm
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold {{ $isDeficit ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800' }}">
                                    {{ $isDeficit ? 'Defisit Air (' . number_format($wb?->deficit_surplus, 1) . ' mm)' : 'Kondisi Cukup / Surplus' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('water-balance.show', $land->id) }}" class="px-3 py-1 rounded-lg bg-cyan-50 hover:bg-cyan-100 text-cyan-800 font-semibold transition">
                                    Rincian Buku Kas &rarr;
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
