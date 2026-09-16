<?php

namespace Database\Seeders;

use App\Models\Land;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class LandSeeder extends Seeder
{
    public function run(): void
    {
        $today = Carbon::today();

        $lands = [
            [
                'kode_lahan' => 'LHK-SMP-001',
                'nama_lahan' => 'Lahan Sawah Padi Ciherang Gapura Barat',
                'desa' => 'Gapura Barat',
                'kecamatan' => 'Gapura',
                'kabupaten' => 'Sumenep',
                'polygon_geojson' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [113.9442, -6.9942],
                        [113.9458, -6.9940],
                        [113.9460, -6.9958],
                        [113.9444, -6.9960],
                        [113.9442, -6.9942],
                    ]],
                ],
                'luas' => 1.20,
                'luas_m2' => 12000,
                'latitude' => -6.9950,
                'longitude' => 113.9451,
                'jenis_tanaman' => 'Padi Ciherang',
                'tanggal_tanam' => $today->copy()->subDays(58)->format('Y-m-d'), // fase reproduktif
                'jenis_tanah' => 'Alluvial (Entisol)',
                'status' => 'Aktif',
                'catatan' => 'Sawah semi-irigasi tadah hujan, saat ini memasuki fase bunting & pembungaan (kebutuhan air tinggi).',
            ],
            [
                'kode_lahan' => 'LHK-SMP-002',
                'nama_lahan' => 'Lahan Tegalan Jagung Hibrida Lenteng Timur',
                'desa' => 'Lenteng Timur',
                'kecamatan' => 'Lenteng',
                'kabupaten' => 'Sumenep',
                'polygon_geojson' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [113.7812, -7.0372],
                        [113.7828, -7.0370],
                        [113.7830, -7.0388],
                        [113.7814, -7.0390],
                        [113.7812, -7.0372],
                    ]],
                ],
                'luas' => 0.85,
                'luas_m2' => 8500,
                'latitude' => -7.0380,
                'longitude' => 113.7821,
                'jenis_tanaman' => 'Jagung Hibrida',
                'tanggal_tanam' => $today->copy()->subDays(42)->format('Y-m-d'), // fase vegetatif aktif
                'jenis_tanah' => 'Mediteran Merah (Alfisol)',
                'status' => 'Aktif',
                'catatan' => 'Lahan tegalan perbukitan kapur, pasokan air mengandalkan sumur bor dangkal.',
            ],
            [
                'kode_lahan' => 'LHK-SMP-003',
                'nama_lahan' => 'Lahan Tembakau Prancak Guluk-Guluk',
                'desa' => 'Guluk-Guluk',
                'kecamatan' => 'Guluk-Guluk',
                'kabupaten' => 'Sumenep',
                'polygon_geojson' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [113.6830, -7.0010],
                        [113.6852, -7.0008],
                        [113.6854, -7.0030],
                        [113.6832, -7.0032],
                        [113.6830, -7.0010],
                    ]],
                ],
                'luas' => 1.50,
                'luas_m2' => 15000,
                'latitude' => -7.0020,
                'longitude' => 113.6842,
                'jenis_tanaman' => 'Tembakau Prancak',
                'tanggal_tanam' => $today->copy()->subDays(65)->format('Y-m-d'), // fase mid-season
                'jenis_tanah' => 'Grumosol (Vertisol)',
                'status' => 'Aktif',
                'catatan' => 'Lahan tembakau gunung di Madura barat, tanah vertisol retak saat kemarau panjang.',
            ],
            [
                'kode_lahan' => 'LHK-SMP-004',
                'nama_lahan' => 'Sentra Bawang Merah Rubaru',
                'desa' => 'Rubaru',
                'kecamatan' => 'Rubaru',
                'kabupaten' => 'Sumenep',
                'polygon_geojson' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [113.8112, -6.9522],
                        [113.8128, -6.9520],
                        [113.8130, -6.9538],
                        [113.8114, -6.9540],
                        [113.8112, -6.9522],
                    ]],
                ],
                'luas' => 0.75,
                'luas_m2' => 7500,
                'latitude' => -6.9530,
                'longitude' => 113.8121,
                'jenis_tanaman' => 'Bawang Merah Rubaru',
                'tanggal_tanam' => $today->copy()->subDays(45)->format('Y-m-d'), // fase pembentukan umbi
                'jenis_tanah' => 'Regosol (Entisol)',
                'status' => 'Aktif',
                'catatan' => 'Varietas lokal unggulan nasional tahan simpan, butuh air presisi saat pembesaran umbi.',
            ],
            [
                'kode_lahan' => 'LHK-SMP-005',
                'nama_lahan' => 'Kebun Cabai Jamu Aengdake Bluto',
                'desa' => 'Aengdake',
                'kecamatan' => 'Bluto',
                'kabupaten' => 'Sumenep',
                'polygon_geojson' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [113.8140, -7.0940],
                        [113.8160, -7.0938],
                        [113.8162, -7.0960],
                        [113.8142, -7.0962],
                        [113.8140, -7.0940],
                    ]],
                ],
                'luas' => 1.10,
                'luas_m2' => 11000,
                'latitude' => -7.0950,
                'longitude' => 113.8151,
                'jenis_tanaman' => 'Cabai Jamu',
                'tanggal_tanam' => $today->copy()->subDays(120)->format('Y-m-d'), // fase pembungaan & buah
                'jenis_tanah' => 'Mediteran Merah (Alfisol)',
                'status' => 'Aktif',
                'catatan' => 'Tanaman rempah khas Madura dengan panjat pohon kapuk, rentan rontok bunga bila kekeringan.',
            ],
            [
                'kode_lahan' => 'LHK-SMP-006',
                'nama_lahan' => 'Sawah Padi Irigasi Teknis Saronggi',
                'desa' => 'Saronggi',
                'kecamatan' => 'Saronggi',
                'kabupaten' => 'Sumenep',
                'polygon_geojson' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [113.8638, -7.0638],
                        [113.8662, -7.0636],
                        [113.8664, -7.0662],
                        [113.8640, -7.0664],
                        [113.8638, -7.0638],
                    ]],
                ],
                'luas' => 2.00,
                'luas_m2' => 20000,
                'latitude' => -7.0650,
                'longitude' => 113.8651,
                'jenis_tanaman' => 'Padi IR64',
                'tanggal_tanam' => $today->copy()->subDays(35)->format('Y-m-d'), // fase vegetatif
                'jenis_tanah' => 'Alluvial (Entisol)',
                'status' => 'Aktif',
                'catatan' => 'Mendapat saluran sekunder DI Saronggi, pasokan relatif stabil kecuali musim kemarau puncak.',
            ],
            [
                'kode_lahan' => 'LHK-SMP-007',
                'nama_lahan' => 'Tegalan Jagung Madura Ganding Barat',
                'desa' => 'Ganding',
                'kecamatan' => 'Ganding',
                'kabupaten' => 'Sumenep',
                'polygon_geojson' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [113.7212, -7.0242],
                        [113.7228, -7.0240],
                        [113.7230, -7.0258],
                        [113.7214, -7.0260],
                        [113.7212, -7.0242],
                    ]],
                ],
                'luas' => 0.65,
                'luas_m2' => 6500,
                'latitude' => -7.0250,
                'longitude' => 113.7221,
                'jenis_tanaman' => 'Jagung Madura',
                'tanggal_tanam' => $today->copy()->subDays(50)->format('Y-m-d'), // fase pembungaan
                'jenis_tanah' => 'Litosol (Entisol)',
                'status' => 'Aktif',
                'catatan' => 'Lahan tanah dangkal berbatu kapur, sangat rentan stres kekeringan jika 5 hari tanpa hujan.',
            ],
            [
                'kode_lahan' => 'LHK-SMP-008',
                'nama_lahan' => 'Lahan Kacang Tanah Pesisir Ambunten',
                'desa' => 'Ambunten Barat',
                'kecamatan' => 'Ambunten',
                'kabupaten' => 'Sumenep',
                'polygon_geojson' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [113.7640, -6.9110],
                        [113.7660, -6.9108],
                        [113.7662, -6.9130],
                        [113.7642, -6.9132],
                        [113.7640, -6.9110],
                    ]],
                ],
                'luas' => 0.95,
                'luas_m2' => 9500,
                'latitude' => -6.9120,
                'longitude' => 113.7651,
                'jenis_tanaman' => 'Kacang Tanah',
                'tanggal_tanam' => $today->copy()->subDays(62)->format('Y-m-d'), // fase pengisian polong
                'jenis_tanah' => 'Regosol (Entisol)',
                'status' => 'Aktif',
                'catatan' => 'Lahan pesisir pantai utara berpasir, laju infiltrasi tinggi membutuhkan frekuensi siram rutin.',
            ],
            [
                'kode_lahan' => 'LHK-SMP-009',
                'nama_lahan' => 'Lahan Kedelai Dasuk Barat',
                'desa' => 'Dasuk Barat',
                'kecamatan' => 'Dasuk',
                'kabupaten' => 'Sumenep',
                'polygon_geojson' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [113.8608, -6.9268],
                        [113.8632, -6.9266],
                        [113.8634, -6.9292],
                        [113.8610, -6.9294],
                        [113.8608, -6.9268],
                    ]],
                ],
                'luas' => 1.30,
                'luas_m2' => 13000,
                'latitude' => -6.9280,
                'longitude' => 113.8621,
                'jenis_tanaman' => 'Kedelai',
                'tanggal_tanam' => $today->copy()->subDays(55)->format('Y-m-d'), // fase pembentukan polong
                'jenis_tanah' => 'Mediteran Merah (Alfisol)',
                'status' => 'Aktif',
                'catatan' => 'Kedelai lahan kering pasca panen padi gadu, membutuhkan kelembapan tanah cukup untuk polong isi.',
            ],
            [
                'kode_lahan' => 'LHK-SMP-010',
                'nama_lahan' => 'Kebun Cabai Merah Manding Laok',
                'desa' => 'Manding Laok',
                'kecamatan' => 'Manding',
                'kabupaten' => 'Sumenep',
                'polygon_geojson' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [113.8812, -6.9642],
                        [113.8828, -6.9640],
                        [113.8830, -6.9658],
                        [113.8814, -6.9660],
                        [113.8812, -6.9642],
                    ]],
                ],
                'luas' => 0.80,
                'luas_m2' => 8000,
                'latitude' => -6.9650,
                'longitude' => 113.8821,
                'jenis_tanaman' => 'Cabai Merah Besar',
                'tanggal_tanam' => $today->copy()->subDays(70)->format('Y-m-d'), // fase pembesaran buah
                'jenis_tanah' => 'Grumosol (Vertisol)',
                'status' => 'Aktif',
                'catatan' => 'Sistem mulsa plastik hitam perak dengan irigasi tetes manual dari tandon embung.',
            ],
        ];

        foreach ($lands as $landData) {
            Land::updateOrCreate(
                ['kode_lahan' => $landData['kode_lahan']],
                $landData
            );
        }
    }
}
