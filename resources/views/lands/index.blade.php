@extends('layouts.app')

@section('title', 'Manajemen Lahan Pertanian')
@section('page_title', 'Manajemen Lahan Pertanian')
@section('page_subtitle', 'Data Spasial Polygon GeoJSON dan Karakteristik Agronomis Lahan di Kabupaten Sumenep')

@section('header_actions')
<a href="{{ route('lands.create') }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold shadow-sm transition">
    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Tambah Lahan Baru
</a>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Information Card -->
    <div class="p-4 rounded-2xl bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/80 flex items-start justify-between">
        <div class="flex items-start space-x-3">
            <div class="p-2 rounded-xl bg-emerald-600 text-white mt-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h4 class="text-sm font-bold text-emerald-950">Basis Data Spasial Parcel Lahan</h4>
                <p class="text-xs text-emerald-800/90 mt-0.5 max-w-3xl leading-relaxed">
                    Setiap unit lahan terdefinisi dalam format poligon <strong>GeoJSON (WGS84 EPSG:4326)</strong> dengan koordinat batas fisik riil di wilayah Kabupaten Sumenep. Data ini menjadi jangkar spasial untuk ekstraksi otomatis nilai piksel Sentinel-2 L2A dan interpolasi presipitasi.
                </p>
            </div>
        </div>
        <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold whitespace-nowrap">
            {{ $lands->total() }} Lahan Terdaftar
        </span>
    </div>

    <!-- Table of Lands -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-4">Kode & Nama Lahan</th>
                        <th class="py-3.5 px-4">Wilayah (Kec./Desa)</th>
                        <th class="py-3.5 px-4">Komoditas & Umur</th>
                        <th class="py-3.5 px-4">Jenis Tanah</th>
                        <th class="py-3.5 px-4">Luas Lahan</th>
                        <th class="py-3.5 px-4">Status Risiko</th>
                        <th class="py-3.5 px-4">Status Budidaya</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($lands as $land)
                        @php
                            $drought = $land->droughtAnalyses->first();
                            $status = $drought?->status ?? 'HIJAU';
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-4">
                                <a href="{{ route('lands.show', $land->id) }}" class="font-bold text-slate-900 hover:text-brand-600 transition">
                                    {{ $land->nama_lahan }}
                                </a>
                                <div class="font-mono text-[11px] text-slate-400 mt-0.5">{{ $land->kode_lahan }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-slate-800">Kec. {{ $land->kecamatan }}</div>
                                <div class="text-slate-400 text-[11px]">Desa {{ $land->desa }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-emerald-800">{{ $land->jenis_tanaman }}</div>
                                <div class="text-slate-500 text-[11px]">{{ $land->umur_tanaman }} HST (Tanam: {{ \Carbon\Carbon::parse($land->tanggal_tanam)->format('d/m/Y') }})</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-medium">
                                    {{ $land->jenis_tanah }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono">
                                <strong>{{ $land->luas }}</strong> Ha
                                <div class="text-[10px] text-slate-400">{{ number_format($land->luas_m2 ?? $land->luas * 10000, 0, ',', '.') }} m²</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold {{ $status === 'MERAH' ? 'bg-red-100 text-red-800' : ($status === 'ORANYE' ? 'bg-orange-100 text-orange-800' : ($status === 'KUNING' ? 'bg-yellow-100 text-yellow-800' : 'bg-emerald-100 text-emerald-800')) }}">
                                    {{ $drought?->status_label ?? 'Air Cukup' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $land->status === 'Aktif' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                    <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $land->status === 'Aktif' ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                    {{ $land->status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1 whitespace-nowrap">
                                <a href="{{ route('lands.show', $land->id) }}" class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 inline-flex items-center" title="Buka Dossier">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <a href="{{ route('lands.edit', $land->id) }}" class="p-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 inline-flex items-center" title="Edit Data Lahan">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <form action="{{ route('lands.destroy', $land->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data lahan ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 inline-flex items-center" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">
                                Belum ada data lahan yang didaftarkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($lands->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $lands->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
