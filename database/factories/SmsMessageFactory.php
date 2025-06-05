<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SmsMessage>
 */
class SmsMessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'content' => 'Please complete your medical history form: [form link]',
            'sent_at' => $this->faker->optional()->dateTimeThisYear(),
            'completed_at' => null,
            'failed_at' => null,
        ];
    }

    public function sentToday()
    {
        return $this->state([
            'sent_at' => now(),
        ]);
    }
}
