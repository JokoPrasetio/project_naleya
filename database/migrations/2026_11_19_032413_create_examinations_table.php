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
            $table->string('code_examination')->nullable();
            $table->string('type_examination_id');
            $table->string('name');
            $table->string('node_type');
            $table->string('barcode_name');
            $table->boolean('active')->default(true);
            $table->string('code_machine')->nullable();
            $table->decimal('price', 14, 2)->nullable();
            $table->integer('sort_order')->nullable();
            $table->string('method_examination')->nullable();
            $table->string('unit')->nullable();
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
