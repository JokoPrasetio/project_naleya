<?php

namespace Database\Factories;

use App\Models\Examination;
use App\Models\TypeExamination;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Examination>
 */
class ExaminationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'parent_id' => '0',
            'code_examination' => fake()->unique()->bothify('EX-####'),
            'type_examination_id' => TypeExamination::factory(),
            'name' => fake()->words(2, true),
            'node_type' => 'item',
            'barcode_name' => fake()->word(),
            'active' => true,
            'code_machine' => null,
            'price' => 25000,
            'sort_order' => 0,
            'method_examination' => null,
            'unit' => null,
        ];
    }
}
