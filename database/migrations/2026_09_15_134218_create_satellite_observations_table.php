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
        Schema::create('satellite_observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('land_id')->constrained('lands')->cascadeOnDelete();
            $table->date('observation_date');
            $table->string('image_url')->nullable();
            $table->decimal('cloud_percentage', 5, 2)->default(0);
            $table->decimal('b4_red', 6, 4)->comment('Band 4 Red surface reflectance');
            $table->decimal('b8_nir', 6, 4)->comment('Band 8 NIR surface reflectance');
            $table->decimal('ndvi', 6, 4)->comment('Normalized Difference Vegetation Index');
            $table->enum('source', ['SENTINEL_2', 'DUMMY'])->default('DUMMY');
            $table->timestamps();

            $table->index(['land_id', 'observation_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('satellite_observations');
    }
};
