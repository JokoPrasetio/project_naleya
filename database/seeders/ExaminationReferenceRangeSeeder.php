<?php

namespace Database\Seeders;

use App\Models\ExaminationReferenceRange;
use Illuminate\Database\Seeder;

class ExaminationReferenceRangeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ExaminationReferenceRange::factory()->create();
    }
}
