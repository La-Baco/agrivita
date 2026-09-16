@extends('layouts.app')

@section('title', 'Audit Risiko Kekeringan - ' . $land->nama_lahan)
@section('page_title', 'Audit Risiko Kekeringan: ' . $land->nama_lahan)
@section('page_subtitle', 'Penjelasan Ilmiah Rinci dan Rantai Logika Penentuan Status Risiko Lahan')

@section('header_actions')
<a href="{{ route('drought.index') }}" class="inline-flex items-center px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
    &larr; Kembali ke Matriks Risiko
</a>
@endsection

@section('content')
<div class="space-y-6">

    @php
        $status = $latestAnalysis?->status ?? 'HIJAU';
        $metrics = $latestAnalysis?->metrics_json ?? [];
    @endphp

    <!-- Top Assessment Status Banner -->
    <div class="p-6 rounded-2xl border bg-white shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Status Analisis Kekeringan Lahan</span>
            <div class="flex items-center space-x-3 mt-1.5">
                <span class="px-3.5 py-1 rounded-xl font-black text-sm uppercase {{ $status === 'MERAH' ? 'bg-red-600 text-white' : ($status === 'ORANYE' ? 'bg-orange-500 text-white' : ($status === 'KUNING' ? 'bg-yellow-400 text-slate-900' : 'bg-emerald-600 text-white')) }}">
                    {{ $status }} • {{ $latestAnalysis?->status_label }}
                </span>
                <span class="text-2xl font-bold font-mono text-slate-800">
                    Skor Gabungan: {{ $metrics['total_score'] ?? 0 }}/100
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                Lahan: <strong>{{ $land->nama_lahan }}</strong> ({{ $land->desa }}, Kec. {{ $land->kecamatan }}) • Komoditas: {{ $land->jenis_tanaman }} ({{ $land->umur_tanaman }} HST)
            </p>
        </div>

        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-600 max-w-sm">
            <strong>Penafian Ilmiah:</strong> Penilaian ini adalah hasil kalkulasi rumus transparan, bukan ramalan AI. Menggabungkan dinamika curah hujan aktual, neraca air, reflektansi spektral satelit, dan prakiraan BMKG.
        </div>
    </div>

    <!-- Transparent Score Breakdown (4 Components) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <!-- 1. Neraca Air (35%) -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-2">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                <h4 class="font-bold text-slate-900 text-sm">1. Indikator Neraca Air (Bobot: 35%)</h4>
                <span class="font-mono text-xs font-bold px-2 py-0.5 rounded bg-blue-50 text-blue-700">
                    Skor: {{ $metrics['water_balance_score'] ?? 0 }}/100
                </span>
            </div>
            <div class="text-xs text-slate-600 space-y-1 pt-1">
                <div>Nilai Neraca Air Terukur: <strong class="font-mono {{ ($latestAnalysis?->water_balance_value ?? 0) < 0 ? 'text-rose-600' : 'text-slate-800' }}">{{ $latestAnalysis?->water_balance_value ?? 0 }} mm</strong></div>
                <div>Kontribusi ke Skor Akhir: <strong class="font-mono text-slate-900">{{ number_format(($metrics['water_balance_score'] ?? 0) * 0.35, 2) }} poin</strong></div>
                <p class="text-slate-500 text-[11px] pt-1">
                    Aturan: Jika neraca defisit &gt; 40 mm &rarr; skor 100; jika defisit &gt; 20 mm &rarr; skor 70; jika defisit &gt; 0 mm &rarr; skor 40; jika surplus &rarr; skor 0–20.
                </p>
            </div>
        </div>

        <!-- 2. Hujan 7 Hari (25%) -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-2">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                <h4 class="font-bold text-slate-900 text-sm">2. Curah Hujan 7 Hari (Bobot: 25%)</h4>
                <span class="font-mono text-xs font-bold px-2 py-0.5 rounded bg-blue-50 text-blue-700">
                    Skor: {{ $metrics['rainfall_score'] ?? 0 }}/100
                </span>
            </div>
            <div class="text-xs text-slate-600 space-y-1 pt-1">
                <div>Akumulasi Hujan 7 Hari: <strong class="font-mono text-slate-800">{{ $latestAnalysis?->rainfall_7d ?? 0 }} mm</strong></div>
                <div>Kontribusi ke Skor Akhir: <strong class="font-mono text-slate-900">{{ number_format(($metrics['rainfall_score'] ?? 0) * 0.25, 2) }} poin</strong></div>
                <p class="text-slate-500 text-[11px] pt-1">
                    Aturan: Jika &lt; 5 mm &rarr; skor 100 (hari kering ekstrem); jika &lt; 15 mm &rarr; skor 70; jika &lt; 35 mm &rarr; skor 35; jika &ge; 35 mm &rarr; skor 0.
                </p>
            </div>
        </div>

        <!-- 3. NDVI Sentinel-2 (20%) -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-2">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                <h4 class="font-bold text-slate-900 text-sm">3. Kondisi Vegetasi NDVI (Bobot: 20%)</h4>
                <span class="font-mono text-xs font-bold px-2 py-0.5 rounded bg-emerald-50 text-emerald-700">
                    Skor: {{ $metrics['ndvi_score'] ?? 0 }}/100
                </span>
            </div>
            <div class="text-xs text-slate-600 space-y-1 pt-1">
                <div>NDVI Terkini: <strong class="font-mono text-emerald-700">{{ $latestAnalysis?->ndvi_value ?? 0 }}</strong> • Perubahan: <strong class="font-mono">{{ $latestAnalysis?->ndvi_change_pct ?? 0 }}%</strong></div>
                <div>Kontribusi ke Skor Akhir: <strong class="font-mono text-slate-900">{{ number_format(($metrics['ndvi_score'] ?? 0) * 0.20, 2) }} poin</strong></div>
                <p class="text-slate-500 text-[11px] pt-1">
                    Aturan: Penurunan NDVI &gt; 15% mengindikasikan kerusakan kanopi / stres air serius &rarr; skor 90–100; fluktuasi normal atau kanopi subur &rarr; skor 0–30.
                </p>
            </div>
        </div>

        <!-- 4. Prakiraan Hujan 3 Hari (20%) -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-2">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                <h4 class="font-bold text-slate-900 text-sm">4. Prakiraan Hujan 3 Hari BMKG (Bobot: 20%)</h4>
                <span class="font-mono text-xs font-bold px-2 py-0.5 rounded bg-cyan-50 text-cyan-700">
                    Skor: {{ $metrics['forecast_score'] ?? 0 }}/100
                </span>
            </div>
            <div class="text-xs text-slate-600 space-y-1 pt-1">
                <div>Prakiraan Hujan 3 Hari: <strong class="font-mono text-slate-800">{{ $latestAnalysis?->forecast_rain_3d ?? 0 }} mm</strong> (Kebutuhan ETc: {{ $latestAnalysis?->water_need_mm ?? 0 }} mm)</div>
                <div>Kontribusi ke Skor Akhir: <strong class="font-mono text-slate-900">{{ number_format(($metrics['forecast_score'] ?? 0) * 0.20, 2) }} poin</strong></div>
                <p class="text-slate-500 text-[11px] pt-1">
                    Aturan: Jika prakiraan mencukupi &ge; 100% kebutuhan tanaman &rarr; skor 0; jika menutupi 60–99% &rarr; skor 30; jika menutupi 30–59% &rarr; skor 65; jika &lt; 30% &rarr; skor 100.
                </p>
            </div>
        </div>
    </div>

    <!-- Full Narration Analysis Notes -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-2">
        <h4 class="font-bold text-slate-900 text-sm">Kesimpulan Audit Naratif Sistem</h4>
        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700 leading-relaxed font-medium">
            {{ $latestAnalysis?->analysis_notes ?? 'Data analisis belum tercatat.' }}
        </div>
    </div>

</div>
@endsection
