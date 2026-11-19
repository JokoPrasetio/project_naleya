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
        Schema::create('examination_result_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('examination_id')->constrained('examinations')->cascadeOnDelete();
            $table->string('field_key'); // result, description, impression, suggestion, image
            $table->string('label'); // hasil, deskripsi, kesan, saran, gambar pemeriksaan
            $table->string('field_type'); // text, text area, number, image
            $table->boolean('required')->default(false);
            $table->string('default_value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('examination_result_fields');
    }
};
