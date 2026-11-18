<?php

namespace Database\Factories;

use App\Models\Examination;
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
            'name' => fake()->words(2, true),
            'node_type' => 'examination',
            'header_type' => null,
            'is_orderable' => true,
            'result_type' => 'number',
            'input_mode' => 'manual',
            'price' => '25000.00',
            'is_active' => true,
        ];
    }

    public function service(): static
    {
        return $this->state(fn (): array => [
            'node_type' => 'header', 'header_type' => 'service',
            'is_orderable' => false, 'result_type' => null, 'price' => null,
        ]);
    }

    public function subHeader(): static
    {
        return $this->state(fn (): array => [
            'node_type' => 'sub_header', 'header_type' => null,
            'is_orderable' => false, 'result_type' => null, 'price' => null,
        ]);
    }

    public function package(): static
    {
        return $this->state(fn (): array => [
            'node_type' => 'header', 'header_type' => 'package',
            'is_orderable' => false, 'result_type' => null, 'price' => null,
        ]);
    }
}
