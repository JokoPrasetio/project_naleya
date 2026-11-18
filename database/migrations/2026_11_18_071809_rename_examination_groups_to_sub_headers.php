<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('examinations')
            ->where('node_type', 'header')
            ->where('header_type', 'group')
            ->update(['node_type' => 'sub_header', 'header_type' => null]);
    }

    public function down(): void
    {
        DB::table('examinations')
            ->where('node_type', 'sub_header')
            ->update(['node_type' => 'header', 'header_type' => 'group']);
    }
};
