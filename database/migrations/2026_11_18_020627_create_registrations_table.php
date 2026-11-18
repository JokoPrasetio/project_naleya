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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->string('nodaftar')->unique();
            $table->dateTime('tgl_daftar');
            $table->foreignId('patient_id')->constrained()->restrictOnDelete();
            $table->string('status', 30)->default('menunggu');
            $table->string('sumber', 20);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->date('tgl_kedatangan');
            $table->string('keperluan', 500);
            $table->text('catatan')->nullable();
            $table->foreignId('provider_id')->nullable();
            $table->string('foto_patients')->nullable();
            $table->timestamps();

            $table->index(['tgl_kedatangan', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
