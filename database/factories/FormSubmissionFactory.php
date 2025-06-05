<?php

namespace Database\Factories;

use App\Models\FormSubmission;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FormSubmissionFactory extends Factory
{
    protected $model = FormSubmission::class;

    public function definition(): array
    {
        $started = $this->faker->dateTimeBetween('-2 days', 'now');
        $completed = $this->faker->boolean(70) ? $this->faker->dateTimeBetween($started, 'now') : null;
        $status = $completed ? 'completed' : 'in_progress';
        return [
            'patient_id' => Patient::factory(),
            'user_id' => User::factory(),
            'data' => [
                'field1' => $this->faker->sentence(),
                'field2' => $this->faker->randomNumber(),
            ],
            'status' => $status,
            'started_at' => $started,
            'completed_at' => $completed,
        ];
    }
} 