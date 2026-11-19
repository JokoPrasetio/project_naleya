<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('examination_result_fields');
        Schema::dropIfExists('examination_machine_codes');
        Schema::dropIfExists('examination_abnormal_values');
        Schema::dropIfExists('examination_normal_values');
        Schema::dropIfExists('examination_result_options');
        Schema::dropIfExists('examination_reference_ranges');
        Schema::dropIfExists('package_services');
        Schema::dropIfExists('examinations');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data dan skema Master Pemeriksaan tidak dapat dipulihkan otomatis.
    }
};
