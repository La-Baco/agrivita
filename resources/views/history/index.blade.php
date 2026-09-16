@extends('layouts.app')

@section('title', 'Riwayat & Tren Lahan')
@section('page_title', 'Riwayat & Tren Pemantauan Lahan')
@section('page_subtitle', 'Pilih salah satu lahan pertanian untuk meninjau riwayat deret waktu multivariat')

@section('content')
<div class="space-y-6">

    <!-- Land Selection Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach ($lands as $land)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 flex flex-col justify-between hover:border-brand-300 hover:shadow-md transition">
                <div>
                    <div class="flex items-start justify-between">
                        <span class="font-mono text-[10px] text-slate-400">{{ $land->kode_lahan }}</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                            {{ $land->status }}
                        </span>
                    </div>
                    <h4 class="font-bold text-slate-900 text-sm mt-1">{{ $land->nama_lahan }}</h4>
                    <p class="text-xs text-slate-500">{{ $land->desa }}, Kec. {{ $land->kecamatan }}</p>

                    <div class="mt-4 pt-3 border-t border-slate-100 text-xs space-y-1 text-slate-600">
                        <div>Komoditas: <strong>{{ $land->jenis_tanaman }}</strong> ({{ $land->umur_tanaman }} HST)</div>
                        <div>Luas: <strong>{{ $land->luas }} Ha</strong> • Tanah: {{ $land->jenis_tanah }}</div>
                    </div>
                </div>

                <div class="mt-5 pt-3 border-t border-slate-100">
                    <a href="{{ route('history.show', $land->id) }}" class="w-full py-2 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs text-center block transition">
                        Buka Riwayat Deret Waktu &rarr;
                    </a>
                </div>
            </div>
        @endforeach
    </div>

</div>
@endsection
