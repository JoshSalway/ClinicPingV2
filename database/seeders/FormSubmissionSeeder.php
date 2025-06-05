<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FormSubmission;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;

class FormSubmissionSeeder extends Seeder
{
    public function run(): void
    {
        $today = Carbon::now()->format('Y-m-d');
        $patients = Patient::whereDate('appointment_at', $today)->get();
        foreach ($patients as $patient) {
            $userId = $patient->user_id;
            $status = rand(0, 1) ? 'completed' : 'in_progress';
            $started = Carbon::parse($patient->appointment_at)->subMinutes(rand(10, 60));
            $completed = $status === 'completed' ? $started->copy()->addMinutes(rand(5, 30)) : null;
            FormSubmission::factory()->create([
                'patient_id' => $patient->id,
                'user_id' => $userId,
                'status' => $status,
                'started_at' => $started,
                'completed_at' => $completed,
            ]);
        }
    }
} 