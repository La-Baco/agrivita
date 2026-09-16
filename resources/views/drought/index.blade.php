@extends('layouts.app')

@section('title', 'Analisis Risiko Kekeringan')
@section('page_title', 'Analisis Risiko Kekeringan Lahan')
@section('page_subtitle', 'Penetapan Status Risiko Berbasis Pembobotan Aturan Terbuka (Rule-Based Non-AI/ML)')

@section('content')
<div class="space-y-6">

    <!-- 1. Transparent Mathematical Model Banner -->
    <div class="p-5 rounded-2xl bg-gradient-to-r from-rose-50 via-amber-50 to-orange-50 border border-rose-200">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center space-x-2 mb-1">
                    <span class="px-2.5 py-0.5 rounded-lg bg-rose-600 text-white font-bold text-xs">Model Statistik & Aturan Terbuka</span>
                    @if (config('app.data_mode') === 'DEMO')
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">DATA DEMO</span>
                    @else
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">DATA RIIL</span>
                    @endif
                </div>
                <h4 class="font-bold text-slate-900 text-sm">Rumus Skor Risiko Gabungan Kekeringan:</h4>
                <div class="p-2.5 my-2 rounded-xl bg-white border border-rose-200 font-mono text-xs text-rose-900 font-bold inline-block shadow-sm">
                    Skor Total = (Skor Neraca Air × 35%) + (Skor Hujan 7D × 25%) + (Skor NDVI × 20%) + (Skor Prakiraan 3D × 20%)
                </div>
                <p class="text-xs text-slate-600 max-w-3xl leading-relaxed">
                    Sistem ini tidak menggunakan "Black Box AI". Semua keputusan dapat dilacak dan diaudit langkah demi langkah secara ilmiah dan transparan oleh peneliti, penyuluh, dan pengambil kebijakan Dinas Pertanian.
                </p>
            </div>

            <!-- Risk Status Distribution -->
            <div class="grid grid-cols-2 gap-2 text-xs min-w-[240px]">
                <div class="p-2.5 rounded-xl bg-white border border-rose-200 flex items-center justify-between">
                    <span class="font-bold text-rose-700">🔴 MERAH (76-100)</span>
                    <span class="font-mono font-bold text-slate-800">{{ $statusSummary['MERAH'] ?? 0 }} Lahan</span>
                </div>
                <div class="p-2.5 rounded-xl bg-white border border-amber-200 flex items-center justify-between">
                    <span class="font-bold text-amber-700">🟠 ORANYE (51-75)</span>
                    <span class="font-mono font-bold text-slate-800">{{ $statusSummary['ORANYE'] ?? 0 }} Lahan</span>
                </div>
                <div class="p-2.5 rounded-xl bg-white border border-yellow-200 flex items-center justify-between">
                    <span class="font-bold text-yellow-700">🟡 KUNING (26-50)</span>
                    <span class="font-mono font-bold text-slate-800">{{ $statusSummary['KUNING'] ?? 0 }} Lahan</span>
                </div>
                <div class="p-2.5 rounded-xl bg-white border border-emerald-200 flex items-center justify-between">
                    <span class="font-bold text-emerald-700">🟢 HIJAU (0-25)</span>
                    <span class="font-mono font-bold text-slate-800">{{ $statusSummary['HIJAU'] ?? 0 }} Lahan</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Drought Matrix Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h4 class="font-bold text-slate-800 text-sm">Matriks Hasil Analisis Risiko per Lahan</h4>
            <span class="text-xs text-slate-400">Skor 0 (Sangat Basah) s/d 100 (Kritis)</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-4">Lahan & Wilayah</th>
                        <th class="py-3.5 px-4">Neraca Air (35%)</th>
                        <th class="py-3.5 px-4">Hujan 7 Hari (25%)</th>
                        <th class="py-3.5 px-4">NDVI (20%)</th>
                        <th class="py-3.5 px-4">Prakiraan 3D (20%)</th>
                        <th class="py-3.5 px-4">Skor Total</th>
                        <th class="py-3.5 px-4">Status & Kategori</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach ($lands as $land)
                        @php
                            $analysis = $land->droughtAnalyses->first();
                            $status = $analysis?->status ?? 'HIJAU';
                            $metrics = $analysis?->metrics_json ?? [];
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-4">
                                <a href="{{ route('drought.show', $land->id) }}" class="font-bold text-slate-900 hover:text-brand-600 transition">
                                    {{ $land->nama_lahan }}
                                </a>
                                <div class="text-[11px] text-slate-400">{{ $land->desa }}, Kec. {{ $land->kecamatan }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono">
                                <span class="{{ ($analysis?->water_balance_value ?? 0) < 0 ? 'text-rose-600 font-bold' : 'text-slate-700' }}">
                                    {{ number_format($analysis?->water_balance_value ?? 0, 1) }} mm
                                </span>
                                <div class="text-[10px] text-slate-400">Skor: {{ $metrics['water_balance_score'] ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono">
                                {{ number_format($analysis?->rainfall_7d ?? 0, 1) }} mm
                                <div class="text-[10px] text-slate-400">Skor: {{ $metrics['rainfall_score'] ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono">
                                <span class="text-emerald-700 font-bold">{{ number_format($analysis?->ndvi_value ?? 0, 3) }}</span>
                                <div class="text-[10px] text-slate-400">Δ {{ number_format($analysis?->ndvi_change_pct ?? 0, 1) }}% (Skor: {{ $metrics['ndvi_score'] ?? '-' }})</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono">
                                {{ number_format($analysis?->forecast_rain_3d ?? 0, 1) }} mm
                                <div class="text-[10px] text-slate-400">Skor: {{ $metrics['forecast_score'] ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-sm">
                                <span class="px-2 py-1 rounded-lg {{ $status === 'MERAH' ? 'bg-red-100 text-red-800' : ($status === 'ORANYE' ? 'bg-orange-100 text-orange-800' : ($status === 'KUNING' ? 'bg-yellow-100 text-yellow-800' : 'bg-emerald-100 text-emerald-800')) }}">
                                    {{ number_format($metrics['total_score'] ?? 0, 1) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold {{ $status === 'MERAH' ? 'bg-red-600 text-white' : ($status === 'ORANYE' ? 'bg-orange-500 text-white' : ($status === 'KUNING' ? 'bg-yellow-400 text-slate-900' : 'bg-emerald-600 text-white')) }}">
                                    {{ $analysis?->status_label ?? 'Air Cukup' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('drought.show', $land->id) }}" class="px-3 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold transition">
                                    Audit Skor &rarr;
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
