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
        Schema::create('examinations', function (Blueprint $table) {
            $table->id();
            $table->string('parent_id');
            $table->string('code_examination')->nullable(); // kode pemeriksaan
            $table->string('type_examination_id'); // tipe pemeriksaaan
            $table->string('name'); // nama pemeriksaan
            $table->string('node_type'); // header, sub header, item
            $table->string('barcode_name');  // nama pemeriksaan dibarcode
            $table->boolean('active')->default(true);
            $table->string('code_machine')->nullable(); // kode mesin
            $table->decimal('price', 14, 2)->nullable(); // harga
            $table->integer('sort_order')->nullable(); // urutan jika item
            $table->string('method_examination')->nullable(); // metode yang digunakan untuk pemeriksaan
            $table->string('unit')->nullable(); // satuan unit
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('examinations');
    }
};
