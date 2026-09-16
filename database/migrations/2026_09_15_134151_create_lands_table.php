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
        Schema::create('lands', function (Blueprint $table) {
            $table->id();
            $table->string('kode_lahan')->unique();
            $table->string('nama_lahan');
            $table->string('desa');
            $table->string('kecamatan');
            $table->string('kabupaten')->default('Sumenep');
            $table->decimal('latitude', 11, 8);
            $table->decimal('longitude', 11, 8);
            $table->json('polygon_geojson');
            $table->decimal('luas', 10, 2)->comment('Luas dalam hektar (ha)');
            $table->decimal('luas_m2', 12, 2)->nullable()->comment('Luas dalam meter persegi');
            $table->string('jenis_tanaman');
            $table->date('tanggal_tanam');
            $table->string('jenis_tanah');
            $table->string('status')->default('Aktif');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lands');
    }
};
