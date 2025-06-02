<?php

namespace Database\Factories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Patient>
 */
class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'appointment_at' => $this->faker->dateTimeBetween('now', '+2 months'),
            'last_sent_at' => $this->faker->optional(0.7)->dateTimeBetween('-1 month', 'now'),
            'status' => $this->faker->randomElement(['pending', 'sent', 'completed', 'failed']),
        ];
    }
} 