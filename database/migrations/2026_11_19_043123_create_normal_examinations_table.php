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
        Schema::create('normal_examinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('examination_id')->constrained('examinations')->cascadeOnDelete();
            $table->string('classification'); // laki lagi
            $table->string('value')->nullable(); // nilai normal
            $table->string('operator')->nullable(); // > < >= <= == !==
            $table->string('unit')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('normal_examinations');
    }
};
