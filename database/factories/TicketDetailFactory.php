<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TicketDetail>
 */
class TicketDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $generateJson = (rand(0, 1) === 1);
        if ($generateJson) {
            $details = [
                'type' => 'json',
                'content' => [
                    'steps' => $this->faker->paragraphs(3),
                    'attachments' => [
                        $this->faker->imageUrl(),
                        $this->faker->imageUrl(),
                    ],
                ],
            ];
            $detailsJson = json_encode($details, JSON_PRETTY_PRINT);
        } else {
            $details = [
                'type' => 'text',
                'content' => $this->faker->paragraph(),
            ];
            $detailsJson = json_encode($details, JSON_PRETTY_PRINT);
        }

        return [
            'ticket_id' => \App\Models\Ticket::factory(),
            'details' => $detailsJson,
        ];
    }
}
