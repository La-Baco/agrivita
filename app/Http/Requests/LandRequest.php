<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'kode_lahan' => ['required', 'string', 'max:20', 'unique:lands,kode_lahan,'.$this->route('land')],
            'nama_lahan' => ['required', 'string', 'max:100'],
            'desa' => ['required', 'string', 'max:100'],
            'kecamatan' => ['required', 'string', 'max:100'],
            'kabupaten' => ['required', 'string', 'max:100'],
            'polygon_geojson' => ['required', 'string'],
            'luas' => ['required', 'numeric', 'min:0.001'],
            'luas_m2' => ['nullable', 'numeric', 'min:0'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'jenis_tanaman' => ['required', 'string', 'max:100'],
            'tanggal_tanam' => ['required', 'date', 'before_or_equal:today'],
            'jenis_tanah' => ['required', 'string', 'max:100'],
            'status' => ['required', 'string', 'in:Aktif,Bera,Panen'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'kode_lahan.required' => 'Kode lahan wajib diisi.',
            'kode_lahan.unique' => 'Kode lahan sudah terdaftar.',
            'nama_lahan.required' => 'Nama lahan wajib diisi.',
            'desa.required' => 'Desa wajib diisi.',
            'kecamatan.required' => 'Kecamatan wajib diisi.',
            'polygon_geojson.required' => 'Polygon lahan wajib digambar pada peta.',
            'luas.required' => 'Luas lahan wajib diisi.',
            'jenis_tanaman.required' => 'Jenis tanaman wajib dipilih.',
            'tanggal_tanam.required' => 'Tanggal tanam wajib diisi.',
            'tanggal_tanam.before_or_equal' => 'Tanggal tanam tidak boleh lebih dari hari ini.',
            'jenis_tanah.required' => 'Jenis tanah wajib dipilih.',
        ];
    }
}
