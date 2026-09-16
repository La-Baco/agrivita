<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AgriVitaSystemTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::firstOrCreate(
            ['email' => 'admin@agrivita.id'],
            ['name' => 'Admin Riset', 'password' => bcrypt('password')]
        );
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('AgriVita Sumenep');
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@agrivita.id',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($this->user);
        $response->assertRedirect(route('dashboard'));
    }

    public function test_dashboard_can_be_rendered(): void
    {
        $response = $this->actingAs($this->user)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Dashboard');
        $expectedModeLabel = config('app.data_mode') === 'DEMO' ? 'DATA DEMO' : 'DATA RIIL';
        $response->assertSee($expectedModeLabel);
    }

    public function test_lands_index_can_be_rendered(): void
    {
        $response = $this->actingAs($this->user)->get('/lands');
        $response->assertStatus(200);
        $response->assertSee('Manajemen Lahan Pertanian');
    }

    public function test_map_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->user)->get('/map');
        $response->assertStatus(200);
        $response->assertSee('Peta Monitoring Spasial Lahan');
    }

    public function test_api_lands_geojson_endpoint_returns_valid_features(): void
    {
        $response = $this->getJson('/api/lands');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'type',
            'features' => [
                '*' => [
                    'type',
                    'geometry' => ['type', 'coordinates'],
                    'properties' => [
                        'id', 'kode_lahan', 'nama_lahan', 'desa', 'kecamatan',
                        'jenis_tanaman', 'luas', 'drought_status', 'drought_color',
                    ],
                ],
            ],
        ]);
        $this->assertEquals('FeatureCollection', $response->json('type'));
        $this->assertGreaterThanOrEqual(10, count($response->json('features')));
    }

    public function test_rainfall_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->user)->get('/rainfall');
        $response->assertStatus(200);
        $response->assertSee('Pemantauan Curah Hujan Lahan');
    }

    public function test_weather_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->user)->get('/weather');
        $response->assertStatus(200);
        $response->assertSee('Prakiraan Cuaca BMKG Lahan');
    }

    public function test_ndvi_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->user)->get('/ndvi');
        $response->assertStatus(200);
        $response->assertSee('Indeks Vegetasi NDVI (Sentinel-2)');
    }

    public function test_water_balance_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->user)->get('/water-balance');
        $response->assertStatus(200);
        $response->assertSee('Neraca Air Lahan Pertanian');
    }

    public function test_water_needs_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->user)->get('/water-needs');
        $response->assertStatus(200);
        $response->assertSee('Kebutuhan Air Tanaman & Defisit Irigasi');
    }

    public function test_priority_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->user)->get('/priority');
        $response->assertStatus(200);
        $response->assertSee('Prioritas Alokasi & Rekomendasi Pemberian Air');
    }

    public function test_reports_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->user)->get('/reports');
        $response->assertStatus(200);
        $response->assertSee('Laporan Eksekutif Pemantauan Spasial Lahan');
    }

    public function test_settings_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->user)->get('/settings');
        $response->assertStatus(200);
        $response->assertSee('Pengaturan Parameter & Koefisien Tanaman');
    }
}
