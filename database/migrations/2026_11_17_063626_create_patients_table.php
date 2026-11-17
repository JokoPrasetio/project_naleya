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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('nik');
            $table->string('no_rm');
            $table->string('nama');
            $table->string('email');
            $table->date('tgl_lahir');
            $table->string('jenis_kelamin');
            $table->string('gol_darah')->nullable();
            $table->string('alamat')->nullable();
            $table->string('kota')->nullable();
            $table->string('provinsi')->nullable();
            $table->string('kode_zip')->nullable();
            $table->string('rt')->nullable();
            $table->string('rw')->nullable();
            $table->string('warga_negara')->nullable();
            $table->string('kontak_darurat_telp')->nullable();
            $table->string('kontak_darurat_nama')->nullable();
            $table->string('no_bpjs')->nullable();
            $table->string('no_bpjs')->nullable();
            $table->string('no_bpjs')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
