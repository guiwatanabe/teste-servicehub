<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->faker->randomElement(['open', 'in_progress', 'closed']);
        $dueDate = $this->faker->dateTimeInInterval('+1 days', '+30 days');
        $closedAt = null;

        if ($status === 'closed') {
            $dueDate = $this->faker->dateTimeInInterval('-30 days', '-1 days');
            $closedAt = $dueDate;
        }

        return [
            'project_id' => \App\Models\Project::factory(),
            'assigned_to' => \App\Models\UserProfile::factory()->state(['role' => 'employee']),
            'title' => $this->faker->sentence(),
            'status' => $status,
            'priority' => $this->faker->randomElement(['low', 'medium', 'high']),
            'due_date' => $dueDate,
            'closed_at' => $closedAt,
        ];
    }
}
