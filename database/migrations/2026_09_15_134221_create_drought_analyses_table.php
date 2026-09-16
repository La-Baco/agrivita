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
        Schema::create('drought_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('land_id')->constrained('lands')->cascadeOnDelete();
            $table->date('analysis_date');
            $table->decimal('water_balance_value', 8, 2);
            $table->decimal('rainfall_7d', 8, 2);
            $table->decimal('ndvi_value', 6, 4);
            $table->decimal('ndvi_change_pct', 6, 2)->comment('Perubahan NDVI dibanding periode sebelumnya (%)');
            $table->decimal('forecast_rain_3d', 8, 2)->comment('Estimasi akumulasi hujan 3 hari ke depan (mm)');
            $table->decimal('water_need_mm', 8, 2)->comment('Kebutuhan air tanaman (mm)');
            $table->enum('status', ['HIJAU', 'KUNING', 'ORANYE', 'MERAH'])->default('HIJAU');
            $table->string('status_label')->comment('Air Cukup / Defisit Ringan / Risiko Kekeringan / Prioritas Air');
            $table->text('analysis_notes')->nullable();
            $table->json('metrics_json')->nullable();
            $table->timestamps();

            $table->index(['land_id', 'analysis_date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drought_analyses');
    }
};
