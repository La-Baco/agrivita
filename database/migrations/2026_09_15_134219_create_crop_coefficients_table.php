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
        Schema::create('crop_coefficients', function (Blueprint $table) {
            $table->id();
            $table->string('crop_name');
            $table->string('growth_stage')->comment('Awal, Vegetatif, Generatif, Pemasakan');
            $table->integer('stage_days_min')->default(0)->comment('Umur hari mulai');
            $table->integer('stage_days_max')->default(30)->comment('Umur hari akhir');
            $table->decimal('kc', 4, 2)->comment('Nilai koefisien tanaman Kc');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['crop_name', 'stage_days_min', 'stage_days_max']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crop_coefficients');
    }
};
