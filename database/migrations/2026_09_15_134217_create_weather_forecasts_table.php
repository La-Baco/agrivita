<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('weather_forecasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('land_id')->constrained('lands')->cascadeOnDelete();
            $table->dateTime('forecast_datetime');
            $table->decimal('temperature', 5, 2)->comment('Suhu dalam Celcius');
            $table->decimal('humidity', 5, 2)->comment('Kelembapan (%)');
            $table->string('weather')->comment('Cuaca deskripsi, misal Cerah Berawan, Hujan Ringan');
            $table->decimal('wind_speed', 5, 2)->comment('Kecepatan angin km/jam');
            $table->string('wind_direction')->nullable();
            $table->decimal('cloud_cover', 5, 2)->default(0)->comment('Tutupan awan (%)');
            $table->decimal('rainfall_estimate', 8, 2)->default(0)->comment('Estimasi curah hujan (mm)');
            $table->enum('source', ['BMKG', 'DUMMY'])->default('DUMMY');
            $table->timestamps();

            $table->index(['land_id', 'forecast_datetime']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weather_forecasts');
    }
};
