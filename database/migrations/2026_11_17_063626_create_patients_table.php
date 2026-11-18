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
            $table->string('nik', 16)->unique();
            $table->string('no_rm')->unique();
            $table->string('nama');
            $table->string('email')->nullable();
            $table->string('no_telepon', 30)->nullable();
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
            $table->string('no_asuransi_lain')->nullable();
            $table->string('nama_asuransi')->nullable();
            $table->string('ihs_id')->nullable();

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
