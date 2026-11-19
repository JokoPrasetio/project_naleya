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
        Schema::table('examinations', function (Blueprint $table) {
            $table->index('type_examination_id');
            $table->index(['parent_id', 'node_type']);
        });

        Schema::table('normal_examinations', function (Blueprint $table) {
            $table->unsignedBigInteger('examination_id')->change();
            $table->foreign('examination_id')->references('id')->on('examinations')->cascadeOnDelete();
        });

        Schema::table('abnormal_examinations', function (Blueprint $table) {
            $table->unsignedBigInteger('examination_id')->change();
            $table->foreign('examination_id')->references('id')->on('examinations')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('examinations', function (Blueprint $table) {
            $table->dropIndex(['type_examination_id']);
            $table->dropIndex(['parent_id', 'node_type']);
        });

        Schema::table('normal_examinations', function (Blueprint $table) {
            $table->dropForeign(['examination_id']);
            $table->string('examination_id')->change();
        });

        Schema::table('abnormal_examinations', function (Blueprint $table) {
            $table->dropForeign(['examination_id']);
            $table->string('examination_id')->change();
        });
    }
};
