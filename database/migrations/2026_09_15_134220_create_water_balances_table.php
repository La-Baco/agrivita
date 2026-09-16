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
        Schema::create('water_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('land_id')->constrained('lands')->cascadeOnDelete();
            $table->date('calculation_date');
            $table->decimal('initial_water', 8, 2)->default(0)->comment('Kandungan air awal (mm)');
            $table->decimal('effective_rainfall', 8, 2)->default(0)->comment('Hujan efektif (mm)');
            $table->decimal('irrigation', 8, 2)->default(0)->comment('Irigasi masuk (mm)');
            $table->decimal('crop_water_use', 8, 2)->default(0)->comment('Evapotranspirasi tanaman ETc (mm)');
            $table->decimal('water_loss', 8, 2)->default(0)->comment('Perkolasi/kehilangan air (mm)');
            $table->decimal('final_water_balance', 8, 2)->comment('Neraca air akhir = initial + rain + irigasi - ETc - loss (mm)');
            $table->decimal('deficit_surplus', 8, 2)->default(0)->comment('Nilai defisit (-) atau surplus (+) terhadap kapasitas lapang');
            $table->json('details_json')->nullable();
            $table->timestamps();

            $table->index(['land_id', 'calculation_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('water_balances');
    }
};
