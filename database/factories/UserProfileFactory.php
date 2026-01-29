<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserProfile>
 */
class UserProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'company_id' => \App\Models\Company::factory(),
            'role' => $this->faker->randomElement(['admin', 'manager', 'employee']),
            'phone' => $this->faker->phoneNumber(),
            'position' => $this->faker->jobTitle(),
            'address' => $this->faker->address(),
        ];
    }
}
