<?php

namespace Database\Factories;

use App\Models\StudentParent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentParent>
 */
class StudentParentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->numerify('##########'),
            'alternate_phone' => null,
            'occupation' => fake()->jobTitle(),
            'address' => fake()->address(),
            'created_by' => null,
        ];
    }
}
