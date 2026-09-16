<?php

namespace App\Http\Controllers;

use App\Models\Land;
use Illuminate\View\View;

class MapController extends Controller
{
    public function index(): View
    {
        $kecamatanList = Land::distinct()->pluck('kecamatan')->sort()->values();
        $desaList = Land::distinct()->pluck('desa')->sort()->values();
        $jenisTanamanList = Land::distinct()->pluck('jenis_tanaman')->sort()->values();

        return view('map.index', compact('kecamatanList', 'desaList', 'jenisTanamanList'));
    }
}
