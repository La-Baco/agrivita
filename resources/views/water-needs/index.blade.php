@extends('layouts.app')

@section('title', 'Kebutuhan Air Tanaman')
@section('page_title', 'Kebutuhan Air Tanaman & Defisit Irigasi')
@section('page_subtitle', 'Estimasi Kebutuhan Air Tanaman (ETc = ETo × Kc) Dibandingkan dengan Proyeksi Hujan 3 Hari BMKG')

@section('content')
<div class="space-y-6">

    <!-- Information Card -->
    <div class="p-5 rounded-2xl bg-teal-50/80 border border-teal-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-lg bg-teal-700 text-white font-bold text-xs">FAO Irrigation Paper No. 56</span>
                @if (config('app.data_mode') === 'DEMO')
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">DATA DEMO</span>
                @else
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">DATA RIIL</span>
                @endif
            </div>
            <h4 class="font-bold text-slate-900 text-sm">Metodologi Kebutuhan Air Tanaman:</h4>
            <div class="p-2.5 my-2 rounded-xl bg-white border border-teal-300 font-mono text-xs text-teal-900 font-bold inline-block shadow-sm">
                ETc = ETo (4.5 mm/hari) × Kc (Koefisien Fase Pertumbuhan)
            </div>
            <p class="text-xs text-slate-600 max-w-3xl leading-relaxed">
                Tabel di bawah ini membandingkan kebutuhan air evapotranspirasi aktual tanaman terhadap estimasi curah hujan 3 hari mendatang. Jika hujan tidak mencukupi (&lt; 100%), sistem menghitung defisit volume irigasi yang perlu dipenuhi.
            </p>
        </div>
        <div class="p-3.5 rounded-xl bg-white/90 border border-teal-200 text-xs min-w-[200px]">
            <span class="font-bold text-slate-700 uppercase block text-[10px]">Basis Perhitungan:</span>
            <div>ETo Musim Kemarau: <strong>4.5 mm/hari</strong></div>
            <div>Jangka Waktu Evaluasi: <strong>3 Hari</strong></div>
        </div>
    </div>

    <!-- Main Comparison Table per Land -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h4 class="font-bold text-slate-800 text-sm">Evaluasi Kebutuhan Air vs Prakiraan Hujan 3 Hari</h4>
            <span class="text-xs text-slate-400">Satuan: Milimeter (mm)</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-4">Lahan & Wilayah</th>
                        <th class="py-3.5 px-4">Komoditas & Umur</th>
                        <th class="py-3.5 px-4">Fase Pertumbuhan</th>
                        <th class="py-3.5 px-4">Nilai Kc</th>
                        <th class="py-3.5 px-4">Kebutuhan Air (ETc)</th>
                        <th class="py-3.5 px-4">Prakiraan Hujan 3D</th>
                        <th class="py-3.5 px-4">Cakupan Hujan</th>
                        <th class="py-3.5 px-4">Defisit Irigasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach ($lands as $item)
                        @php
                            $land = $item['land'];
                            $isDeficit = $item['deficit_mm'] > 0;
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-4">
                                <a href="{{ route('lands.show', $land->id) }}" class="font-bold text-slate-900 hover:text-brand-600 transition">
                                    {{ $land->nama_lahan }}
                                </a>
                                <div class="text-[11px] text-slate-400">{{ $land->desa }}, Kec. {{ $land->kecamatan }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-slate-800">{{ $land->jenis_tanaman }}</span>
                                <div class="text-[11px] text-slate-400">{{ $land->umur_tanaman }} Hari Setelah Tanam</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-medium">
                                    {{ $item['growth_stage'] }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-emerald-800">
                                {{ number_format($item['kc'], 2) }}
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                {{ number_format($item['etc_mm'], 1) }} mm/hr
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-blue-700">
                                {{ number_format($item['forecast_rain_3d'], 1) }} mm
                            </td>
                            <td class="py-3.5 px-4 font-mono">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold {{ $item['coverage_by_forecast_pct'] >= 100 ? 'bg-emerald-100 text-emerald-800' : ($item['coverage_by_forecast_pct'] >= 50 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                    {{ number_format($item['coverage_by_forecast_pct'], 1) }}%
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold">
                                @if ($isDeficit)
                                    <span class="text-rose-600">
                                        Perlu {{ number_format($item['deficit_mm'], 1) }} mm
                                    </span>
                                @else
                                    <span class="text-emerald-700 font-semibold">
                                        ✓ Tercukupi
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- FAO 56 Reference Crop Coefficient Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h4 class="font-bold text-slate-800 text-sm">Tabel Standar Koefisien Tanaman (Kc FAO-56) di Sumenep</h4>
                <p class="text-xs text-slate-400 mt-0.5">Nilai Kc terkalibrasi per fase umur tanaman komoditas pertanian lokal</p>
            </div>
            <a href="{{ route('settings.index') }}" class="text-xs text-brand-600 hover:text-brand-800 font-semibold">
                Kelola Nilai Kc &rarr;
            </a>
        </div>
        <div class="overflow-x-auto max-h-72">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200/80 sticky top-0">
                    <tr>
                        <th class="py-2.5 px-4">Komoditas Tanaman</th>
                        <th class="py-2.5 px-4">Tahap Pertumbuhan</th>
                        <th class="py-2.5 px-4">Rentang Umur (HST)</th>
                        <th class="py-2.5 px-4">Nilai Kc</th>
                        <th class="py-2.5 px-4">Keterangan Agroklimat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach ($cropCoefficients as $kc)
                        <tr class="hover:bg-slate-50/70">
                            <td class="py-2 px-4 font-bold text-slate-800">{{ $kc->crop_name }}</td>
                            <td class="py-2 px-4">{{ $kc->growth_stage }}</td>
                            <td class="py-2 px-4 font-mono text-slate-600">{{ $kc->stage_days_min }} - {{ $kc->stage_days_max }} hari</td>
                            <td class="py-2 px-4 font-mono font-bold text-emerald-700">{{ number_format($kc->kc, 2) }}</td>
                            <td class="py-2 px-4 text-slate-500 text-[11px]">{{ $kc->description }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
