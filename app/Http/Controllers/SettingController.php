<?php

namespace App\Http\Controllers;

use App\Models\CropCoefficient;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $settings = SystemSetting::orderBy('group')->orderBy('key')->get();
        $cropCoefficients = CropCoefficient::orderBy('crop_name')->orderBy('stage_days_min')->get();

        $groupedCropCoefs = $cropCoefficients->groupBy('crop_name');

        return view('settings.index', compact('settings', 'groupedCropCoefs'));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'settings' => ['nullable', 'array'],
            'settings.*' => ['nullable', 'string', 'max:500'],
        ]);

        foreach ($validated['settings'] ?? [] as $key => $value) {
            SystemSetting::setValue($key, $value);
        }

        return redirect()->route('settings.index')->with('success', 'Pengaturan berhasil disimpan.');
    }

    public function updateCropCoefficient(Request $request, CropCoefficient $cropCoefficient): RedirectResponse
    {
        $validated = $request->validate([
            'kc' => ['required', 'numeric', 'min:0', 'max:3'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $cropCoefficient->update($validated);

        return redirect()->route('settings.index')->with('success', 'Koefisien tanaman berhasil diperbarui.');
    }

    public function syncWeather(): RedirectResponse
    {
        try {
            Artisan::call('agrivita:sync-weather');

            return redirect()->route('settings.index')->with('success', 'Sinkronisasi cuaca dan curah hujan riil berhasil dijalankan untuk seluruh lahan.');
        } catch (\Throwable $e) {
            return redirect()->route('settings.index')->with('error', 'Gagal sinkronisasi cuaca: '.$e->getMessage());
        }
    }

    public function cleanDemo(Request $request): RedirectResponse
    {
        try {
            $withLands = $request->boolean('with_lands');
            Artisan::call('agrivita:clean-demo', [
                '--with-lands' => $withLands,
            ]);

            return redirect()->route('settings.index')->with('success', 'Data demo berhasil dibersihkan. Sistem beralih ke Mode Data Riil.');
        } catch (\Throwable $e) {
            return redirect()->route('settings.index')->with('error', 'Gagal membersihkan data demo: '.$e->getMessage());
        }
    }
}
