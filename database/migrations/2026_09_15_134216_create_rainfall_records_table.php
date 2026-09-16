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
        Schema::create('rainfall_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('land_id')->constrained('lands')->cascadeOnDelete();
            $table->date('observation_date');
            $table->decimal('rainfall_mm', 8, 2);
            $table->enum('source', ['NASA_GPM', 'MANUAL', 'DUMMY'])->default('DUMMY');
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['land_id', 'observation_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rainfall_records');
    }
};
