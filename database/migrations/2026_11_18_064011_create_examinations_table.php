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
            $table->foreignId('parent_id')->nullable()->constrained('examinations')->restrictOnDelete();
            $table->string('code', 50)->nullable()->unique();
            $table->string('name');
            $table->string('node_type', 20);
            $table->string('header_type', 20)->nullable();
            $table->boolean('is_orderable')->default(false);
            $table->string('result_type', 20)->nullable();
            $table->string('input_mode', 20)->default('manual');
            $table->string('unit', 50)->nullable();
            $table->string('method')->nullable();
            $table->decimal('price', 14, 2)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['parent_id', 'sort_order']);
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
