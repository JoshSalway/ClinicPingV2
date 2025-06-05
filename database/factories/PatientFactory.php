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
            'last_sent_at' => null,
            'status' => 'pending',
        ];
    }

    public function appointmentToday()
    {
        return $this->state([
            'appointment_at' => now(),
        ]);
    }
} 