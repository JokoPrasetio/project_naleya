<?php

namespace Database\Factories;

use App\Models\Examination;
use App\Models\ExaminationReferenceRange;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExaminationReferenceRange>
 */
class ExaminationReferenceRangeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'examination_id' => Examination::factory(),
            'min_value' => 10,
            'max_value' => 20,
        ];
    }
}
