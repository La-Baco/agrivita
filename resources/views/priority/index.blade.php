@extends('layouts.app')

@section('title', 'Prioritas Pemberian Air')
@section('page_title', 'Prioritas Alokasi & Rekomendasi Pemberian Air')
@section('page_subtitle', 'Urutan Prioritas Penyaluran Air Irigasi Berdasarkan Tingkat Kritis Lahan dan Penjelasan Alasan Ilmiah Transparan')

@section('content')
<div class="space-y-6">

    <!-- Overview Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-rose-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-rose-500">Prioritas Tinggi (Segera)</p>
                <h3 class="text-2xl font-bold font-mono text-rose-700 mt-1">{{ $stats['tinggi'] }} Lahan</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Alokasi irigasi mendesak</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-lg">
                🔴
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-amber-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-amber-500">Prioritas Sedang (Pantau)</p>
                <h3 class="text-2xl font-bold font-mono text-amber-700 mt-1">{{ $stats['sedang'] }} Lahan</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Pantau 2–3 hari ke depan</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-lg">
                🟠
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-emerald-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-500">Prioritas Rendah (Aman)</p>
                <h3 class="text-2xl font-bold font-mono text-emerald-700 mt-1">{{ $stats['rendah'] }} Lahan</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Kondisi air mencukupi</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-lg">
                🟢
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-blue-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-blue-500">Total Kebutuhan Kuota</p>
                <h3 class="text-2xl font-bold font-mono text-blue-700 mt-1">{{ number_format($stats['total_quota_mm'], 1) }} mm</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Volume kumulatif Sumenep</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-lg">
                💧
            </div>
        </div>
    </div>

    <!-- Ranked Priority Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h4 class="font-bold text-slate-800 text-sm">Peringkat Urutan Alokasi Air Irigasi Lahan</h4>
                <p class="text-xs text-slate-500 mt-0.5">Penetapan urutan ranking transparan berbasis gabungan skor keparahan kekeringan</p>
            </div>
            @if (config('app.data_mode') === 'DEMO')
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">DATA DEMO</span>
            @else
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">DATA RIIL</span>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-4 w-12 text-center">Rank</th>
                        <th class="py-3.5 px-4">Lahan & Wilayah</th>
                        <th class="py-3.5 px-4">Tingkat Prioritas</th>
                        <th class="py-3.5 px-4">Defisit Air</th>
                        <th class="py-3.5 px-4">Estimasi Kuota</th>
                        <th class="py-3.5 px-4">Alasan Ilmiah Transparan (Rationale)</th>
                        <th class="py-3.5 px-4">Rekomendasi Tindakan Teknis</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($recommendations as $index => $rec)
                        @php
                            $land = $rec->land;
                            $p = $rec->priority_level;
                            $isHigh = $p === 'TINGGI';
                            $isMed = $p === 'SEDANG';
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition {{ $isHigh ? 'bg-rose-50/30' : ($isMed ? 'bg-amber-50/30' : '') }}">
                            <td class="py-3.5 px-4 text-center font-mono font-bold text-sm text-slate-500">
                                #{{ $index + 1 }}
                            </td>
                            <td class="py-3.5 px-4">
                                <a href="{{ route('lands.show', $land->id) }}" class="font-bold text-slate-900 hover:text-brand-600 transition">
                                    {{ $land->nama_lahan }}
                                </a>
                                <div class="text-[11px] text-slate-500">{{ $land->desa }}, Kec. {{ $land->kecamatan }}</div>
                                <div class="text-[10px] text-slate-400 mt-0.5">{{ $land->jenis_tanaman }} ({{ $land->umur_tanaman }} HST)</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold {{ $isHigh ? 'bg-rose-100 text-rose-800' : ($isMed ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800') }}">
                                    {{ $isHigh ? '🔴 Prioritas TINGGI' : ($isMed ? '🟠 Prioritas SEDANG' : '🟢 Prioritas RENDAH') }}
                                </span>
                                <div class="text-[10px] font-mono text-slate-400 mt-0.5">Skor: {{ number_format($rec->priority_score, 1) }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold {{ $rec->water_deficit_mm > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                {{ number_format($rec->water_deficit_mm, 1) }} mm
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-blue-700">
                                {{ number_format($rec->water_quota_estimate_mm, 1) }} mm
                            </td>
                            <td class="py-3.5 px-4 max-w-xs text-slate-700 leading-relaxed text-[11px]">
                                {{ $rec->rationale }}
                            </td>
                            <td class="py-3.5 px-4 max-w-sm text-slate-700 leading-relaxed text-[11px]">
                                {{ $rec->recommendation_text }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                Belum ada data rekomendasi prioritas yang dihasilkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
