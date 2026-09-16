@extends('layouts.app')

@section('title', 'Pengaturan Parameter & Koefisien')
@section('page_title', 'Pengaturan Parameter & Koefisien Tanaman (Kc)')
@section('page_subtitle', 'Kalibrasi Bobot Penilaian Kekeringan, Ambang Batas Klasifikasi, dan Parameter Agronomi FAO-56')

@section('content')
<div class="space-y-8 max-w-5xl">

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between shadow-xs">
            <div class="flex items-center space-x-2">
                <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center space-x-2 shadow-xs">
            <svg class="w-4 h-4 text-rose-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @php
        $isDemo = config('app.data_mode') === 'DEMO';
    @endphp

    <!-- 0. Kontrol Mode Data & Sinkronisasi Real-Time -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
        <div class="border-b border-slate-100 pb-4 flex items-center justify-between">
            <div>
                <h4 class="font-bold text-slate-900 text-sm">Mode Operasi Sistem & Sumber Data</h4>
                <p class="text-xs text-slate-500 mt-0.5">Beralih antara mode Demo Simulasi dan mode Data Riil (Live Production)</p>
            </div>
            @if ($isDemo)
                <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                    MODE DEMO SIMULASI
                </span>
            @else
                <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-xs flex items-center space-x-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>MODE DATA RIIL (PRODUKSI)</span>
                </span>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Sync Real Weather Box -->
            <div class="p-4 rounded-xl border border-blue-100 bg-blue-50/40 flex flex-col justify-between space-y-4">
                <div class="space-y-1">
                    <div class="flex items-center space-x-2 text-blue-900 font-bold text-xs">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z"/></svg>
                        <span>Sinkronisasi Cuaca & Hujan Riil</span>
                    </div>
                    <p class="text-[11px] text-slate-600">
                        Menghubungi Open-Meteo API untuk mengambil presipitasi aktual 30 hari dan prakiraan cuaca 3–4 hari ke depan berdasarkan koordinat GPS lahan nyata di Sumenep.
                    </p>
                </div>
                <form action="{{ route('settings.sync-weather') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-xs transition flex items-center justify-center space-x-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Tarik Cuaca Riil Sekarang</span>
                    </button>
                </form>
            </div>

            <!-- Clean Demo Data Box -->
            <div class="p-4 rounded-xl border border-rose-100 bg-rose-50/40 flex flex-col justify-between space-y-4">
                <div class="space-y-1">
                    <div class="flex items-center space-x-2 text-rose-900 font-bold text-xs">
                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <span>Bersihkan Data Demo / Dummy</span>
                    </div>
                    <p class="text-[11px] text-slate-600">
                        Menghapus seluruh transaksi simulasi (cuaca dummy, curah hujan dummy, observasi dummy, neraca air dummy) untuk memastikan sistem bersih.
                    </p>
                </div>
                <form action="{{ route('settings.clean-demo') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membersihkan data demo? Data dummy akan dihapus permanen.');">
                    @csrf
                    <div class="mb-2 flex items-center space-x-2">
                        <input type="checkbox" id="with_lands" name="with_lands" value="1" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500 text-xs">
                        <label for="with_lands" class="text-[11px] text-rose-950 font-medium cursor-pointer">Sertakan hapus 10 lahan demo percontohan</label>
                    </div>
                    <button type="submit" class="w-full px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs shadow-xs transition flex items-center justify-center space-x-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <span>Bersihkan Data Demo Sekarang</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- 1. System Settings Form -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
        <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
            <div>
                <h4 class="font-bold text-slate-900 text-sm">Parameter Sistem & Bobot Penilaian Risiko</h4>
                <p class="text-xs text-slate-500 mt-0.5">Penyesuaian bobot indikator aturan penilaian kekeringan terbuka</p>
            </div>
            @if ($isDemo)
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                    DATA DEMO
                </span>
            @else
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                    DATA AKTIF
                </span>
            @endif
        </div>

        <form action="{{ route('settings.update') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Scoring Weights -->
            <div>
                <h5 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">1. Bobot Komponen Penilaian Kekeringan (Total Harus 100%)</h5>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    @foreach ($settings->where('group', 'scoring') as $setting)
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1" title="{{ $setting->key }}">
                                {{ $setting->label ?? $setting->key }}
                            </label>
                            <input type="text" name="settings[{{ $setting->key }}]" value="{{ old('settings.'.$setting->key, $setting->value) }}"
                                   class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 font-mono font-bold focus:outline-none focus:border-brand-500">
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Risk Thresholds -->
            <div class="pt-4 border-t border-slate-100">
                <h5 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">2. Ambang Batas Skor Status Risiko</h5>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    @foreach ($settings->where('group', 'thresholds') as $setting)
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ $setting->label ?? $setting->key }}
                            </label>
                            <input type="text" name="settings[{{ $setting->key }}]" value="{{ old('settings.'.$setting->key, $setting->value) }}"
                                   class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 font-mono font-bold focus:outline-none focus:border-brand-500">
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- API Configuration -->
            <div class="pt-4 border-t border-slate-100">
                <h5 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">3. Konfigurasi Endpoint API Terbuka (Placeholders)</h5>
                <div class="space-y-3">
                    @foreach ($settings->where('group', 'api') as $setting)
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ $setting->label ?? $setting->key }}
                            </label>
                            <input type="text" name="settings[{{ $setting->key }}]" value="{{ old('settings.'.$setting->key, $setting->value) }}"
                                   class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 font-mono text-slate-600 focus:outline-none focus:border-brand-500">
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-sm transition">
                    Simpan Perubahan Pengaturan
                </button>
            </div>
        </form>
    </div>

    <!-- 2. Crop Coefficients (Kc) Management -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
        <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
            <div>
                <h4 class="font-bold text-slate-900 text-sm">Manajemen Tabel Koefisien Tanaman (Kc FAO-56)</h4>
                <p class="text-xs text-slate-500 mt-0.5">Nilai Kc per fase umur tanam komoditas Kabupaten Sumenep</p>
            </div>
        </div>

        <div class="space-y-6">
            @foreach ($groupedCropCoefs as $cropName => $coefs)
                <div class="border border-slate-200 rounded-xl overflow-hidden">
                    <div class="bg-slate-50 px-4 py-2.5 border-b border-slate-200 font-bold text-xs text-slate-800 flex items-center justify-between">
                        <span>Komoditas: {{ $cropName }}</span>
                        <span class="text-slate-400 font-normal text-[11px]">{{ $coefs->count() }} Fase Pertumbuhan</span>
                    </div>

                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100/60 text-slate-500 uppercase font-semibold text-[10px] border-b border-slate-200">
                            <tr>
                                <th class="py-2 px-3">Fase Pertumbuhan</th>
                                <th class="py-2 px-3">Umur Hari (HST)</th>
                                <th class="py-2 px-3 w-28">Nilai Kc</th>
                                <th class="py-2 px-3">Keterangan</th>
                                <th class="py-2 px-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @foreach ($coefs as $kc)
                                <tr>
                                    <td class="py-2.5 px-3 font-semibold text-slate-800">{{ $kc->growth_stage }}</td>
                                    <td class="py-2.5 px-3 font-mono text-slate-600">{{ $kc->stage_days_min }} - {{ $kc->stage_days_max }} hari</td>
                                    <td class="py-2.5 px-3">
                                        <form action="{{ route('settings.crop-coefficient.update', $kc->id) }}" method="POST" class="flex items-center space-x-1.5" id="formKc{{ $kc->id }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="number" step="0.01" min="0" max="3" name="kc" value="{{ $kc->kc }}"
                                                   class="w-20 px-2 py-1 text-xs rounded-lg border border-slate-300 font-mono font-bold focus:outline-none focus:border-brand-500">
                                            <input type="hidden" name="description" value="{{ $kc->description }}">
                                    </td>
                                    <td class="py-2.5 px-3 text-slate-500 text-[11px]">{{ $kc->description }}</td>
                                    <td class="py-2.5 px-3 text-right">
                                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold text-xs transition">
                                                Update
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
