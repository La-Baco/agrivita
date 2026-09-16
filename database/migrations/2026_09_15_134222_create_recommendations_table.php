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
        Schema::create('recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('land_id')->constrained('lands')->cascadeOnDelete();
            $table->date('recommendation_date');
            $table->enum('priority_level', ['TINGGI', 'SEDANG', 'RENDAH'])->default('RENDAH');
            $table->decimal('priority_score', 5, 2)->default(0)->comment('Skor prioritas 0 - 100');
            $table->text('rationale')->comment('Alasan ilmiah / transparan penetapan prioritas');
            $table->text('recommendation_text')->comment('Rekomendasi teknis alokasi/pemberian air');
            $table->decimal('water_deficit_mm', 8, 2)->default(0)->comment('Defisit air terhitung');
            $table->decimal('water_quota_estimate_mm', 8, 2)->default(0)->comment('Estimasi kebutuhan kuota air');
            $table->timestamps();

            $table->index(['land_id', 'recommendation_date']);
            $table->index('priority_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recommendations');
    }
};
