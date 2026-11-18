<?php

namespace Database\Seeders;

use App\Models\ExaminationResultOption;
use Illuminate\Database\Seeder;

class ExaminationResultOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ExaminationResultOption::factory()->create();
    }
}
