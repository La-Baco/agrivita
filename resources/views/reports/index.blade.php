@extends('layouts.app')

@section('title', 'Laporan Riset Eksekutif')
@section('page_title', 'Laporan Eksekutif Pemantauan Spasial Lahan')
@section('page_subtitle', 'Rekapitulasi Komprehensif Kondisi Kekeringan dan Rekomendasi Alokasi Air Kabupaten Sumenep')

@section('header_actions')
<button type="button" onclick="window.print()" class="inline-flex items-center px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold shadow-sm transition">
    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
    </svg>
    Cetak / Simpan PDF
</button>
@endsection

@section('content')
<div class="space-y-6 max-w-6xl mx-auto print:max-w-none print:m-0">

    <!-- Report Document Header (Print Visible) -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm print:border-none print:shadow-none print:p-0">
        <div class="border-b-2 border-slate-900 pb-4 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 rounded-xl bg-brand-600 text-white flex items-center justify-center font-bold text-xl">
                    AV
                </div>
                <div>
                    <h2 class="text-lg font-black text-slate-900 uppercase tracking-tight">AgriVita GIS - Laporan Spasial Lahan Pertanian</h2>
                    <p class="text-xs text-slate-600">Sistem Pemantauan Hujan, Kekeringan, Vegetasi, dan Kebutuhan Air Lahan Pertanian</p>
                    <p class="text-[11px] text-slate-400">Wilayah Kajian: Kabupaten Sumenep, Madura, Provinsi Jawa Timur</p>
                </div>
            </div>
            <div class="text-right">
                @if (config('app.data_mode') === 'DEMO')
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                        DATA DEMO
                    </span>
                @else
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                        DATA RIIL
                    </span>
                @endif
                <p class="text-xs font-mono font-bold text-slate-800 mt-1">Tanggal Terbit: {{ $reportDate }}</p>
                <p class="text-[11px] text-slate-400">Dokumen Riset Prototipe</p>
            </div>
        </div>

        <!-- Summary Statistics Row -->
        <div class="grid grid-cols-4 gap-4 py-5 border-b border-slate-100 text-center">
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                <span class="text-[11px] font-semibold text-slate-400 uppercase block">Total Unit Lahan</span>
                <span class="text-xl font-bold font-mono text-slate-900">{{ $totalLahan }} Lahan</span>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                <span class="text-[11px] font-semibold text-slate-400 uppercase block">Luas Total Terpantau</span>
                <span class="text-xl font-bold font-mono text-slate-900">{{ number_format($totalLuas, 2) }} Ha</span>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                <span class="text-[11px] font-semibold text-slate-400 uppercase block">Lahan Kritis (Merah)</span>
                <span class="text-xl font-bold font-mono text-rose-700">{{ $droughtSummary['MERAH'] ?? 0 }} Lahan</span>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                <span class="text-[11px] font-semibold text-slate-400 uppercase block">Lahan Waspada (Oranye)</span>
                <span class="text-xl font-bold font-mono text-amber-700">{{ $droughtSummary['ORANYE'] ?? 0 }} Lahan</span>
            </div>
        </div>

        <!-- Consolidated Table of Lands -->
        <div class="pt-5 overflow-x-auto">
            <h4 class="font-bold text-slate-900 text-sm mb-3">Tabel Konsolidasi Status Lahan & Rekomendasi Alokasi Air</h4>
            <table class="w-full text-left text-xs border border-slate-200">
                <thead class="bg-slate-100 text-slate-700 font-bold uppercase text-[10px] border-b border-slate-300">
                    <tr>
                        <th class="py-2.5 px-3 border-r border-slate-200">Kode & Lahan</th>
                        <th class="py-2.5 px-3 border-r border-slate-200">Kecamatan / Desa</th>
                        <th class="py-2.5 px-3 border-r border-slate-200">Komoditas & Umur</th>
                        <th class="py-2.5 px-3 border-r border-slate-200">Luas</th>
                        <th class="py-2.5 px-3 border-r border-slate-200">NDVI</th>
                        <th class="py-2.5 px-3 border-r border-slate-200">Neraca Air</th>
                        <th class="py-2.5 px-3 border-r border-slate-200">Status Risiko</th>
                        <th class="py-2.5 px-3 border-r border-slate-200">Prioritas</th>
                        <th class="py-2.5 px-3">Defisit Kuota</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-800">
                    @foreach ($lands as $land)
                        @php
                            $drought = $land->droughtAnalyses->first();
                            $rec = $land->recommendations->first();
                            $obs = $land->satelliteObservations->first();
                            $wb = $land->waterBalances->first();
                            $status = $drought?->status ?? 'HIJAU';
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="py-2.5 px-3 border-r border-slate-200 font-bold">
                                {{ $land->nama_lahan }}
                                <div class="font-mono text-[10px] text-slate-400">{{ $land->kode_lahan }}</div>
                            </td>
                            <td class="py-2.5 px-3 border-r border-slate-200">
                                Kec. {{ $land->kecamatan }}, Desa {{ $land->desa }}
                            </td>
                            <td class="py-2.5 px-3 border-r border-slate-200">
                                {{ $land->jenis_tanaman }} ({{ $land->umur_tanaman }} HST)
                            </td>
                            <td class="py-2.5 px-3 border-r border-slate-200 font-mono">
                                {{ $land->luas }} Ha
                            </td>
                            <td class="py-2.5 px-3 border-r border-slate-200 font-mono font-bold text-emerald-700">
                                {{ number_format($obs?->ndvi ?? 0, 3) }}
                            </td>
                            <td class="py-2.5 px-3 border-r border-slate-200 font-mono {{ ($wb?->deficit_surplus ?? 0) < 0 ? 'text-rose-600 font-bold' : '' }}">
                                {{ number_format($wb?->deficit_surplus ?? 0, 1) }} mm
                            </td>
                            <td class="py-2.5 px-3 border-r border-slate-200 font-bold">
                                <span class="{{ $status === 'MERAH' ? 'text-red-700' : ($status === 'ORANYE' ? 'text-orange-700' : ($status === 'KUNING' ? 'text-yellow-700' : 'text-emerald-700')) }}">
                                    {{ $drought?->status_label ?? 'Air Cukup' }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 border-r border-slate-200 font-bold">
                                {{ $rec?->priority_level ?? 'RENDAH' }}
                            </td>
                            <td class="py-2.5 px-3 font-mono font-bold text-blue-700">
                                {{ number_format($rec?->water_quota_estimate_mm ?? 0, 1) }} mm
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Signatory Section for Official Research Verification -->
        <div class="mt-8 pt-6 border-t border-slate-200 grid grid-cols-2 gap-8 text-xs text-slate-700">
            <div>
                <p class="font-bold">Penafian Metodologi Ilmiah:</p>
                <p class="text-[11px] text-slate-500 leading-relaxed mt-1">
                    Laporan ini dihasilkan secara otomatis oleh sistem AgriVita GIS dengan perhitungan agronomis terbuka (FAO-56 Blaney-Criddle, USDA-SCS, dan rasio reflektansi Sentinel-2). Pengambilan keputusan alokasi fisik di lapangan harus tetap memverifikasi ketersediaan debit sumber air setempat.
                </p>
            </div>
            <div class="text-right flex flex-col justify-end">
                <p class="text-slate-500">Sumenep, {{ $reportDate }}</p>
                <p class="font-bold text-slate-800 mt-1">Tim Peneliti Sistem Pemantauan Pertanian Spasial</p>
                <div class="h-12"></div>
                <p class="font-bold underline text-slate-900">Dr. Ir. Suryadi, M.Sc / Tim Agrivita</p>
                <p class="text-[10px] text-slate-400">NIP. 19780512 200501 1 003</p>
            </div>
        </div>
    </div>

</div>
@endsection
