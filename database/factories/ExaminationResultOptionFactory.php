<?php

namespace Database\Factories;

use App\Models\Examination;
use App\Models\ExaminationResultOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExaminationResultOption>
 */
class ExaminationResultOptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'examination_id' => Examination::factory()->state(['result_type' => 'choice']),
            'value' => fake()->unique()->word(),
            'label' => fake()->word(),
            'is_normal' => true,
        ];
    }
}
