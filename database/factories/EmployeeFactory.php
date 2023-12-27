<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->unique()->phoneNumber(),
            'pin' => '1234',
            'token' => ''
        ];
    }

    public function asOwner(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'Pemilik',
        ]);
    }

    public function asManager(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'Manajer',
        ]);
    }

    public function asOperator(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'Kasir',
        ]);
    }
}
