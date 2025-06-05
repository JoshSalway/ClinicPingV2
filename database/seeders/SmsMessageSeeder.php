<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Patient;
use App\Models\SmsMessage;
use Carbon\Carbon;
use App\Helpers\PatientStatusHelper;

class SmsMessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $message = 'Please complete your medical history form: [form link]';
        $today = now()->format('Y-m-d');

        // Get all patients with today's appointment, sorted by time
        $todaysPatients = Patient::whereDate('appointment_at', $today)
            ->orderBy('appointment_at')
            ->get();

        // First 3: completed (sent + completed), next 3: sent only, rest: pending only
        $completedPatients = $todaysPatients->take(3);
        $sentPatients = $todaysPatients->slice(3, 3);
        $pendingPatients = $todaysPatients->slice(6);

        // Completed: sent + completed SMS
        foreach ($completedPatients as $patient) {
            $appointment = $patient->appointment_at ?? now()->setTime(11, 0, 0);
            $patient->update(['appointment_at' => $appointment]);
            $offset = rand(10, 20); // minutes before appointment
            $sentAt = (clone $appointment)->subMinutes($offset);
            $sentSms = SmsMessage::factory()->create([
                'content' => $message,
                'status' => 'sent',
                'sent_at' => $sentAt,
            ]);
            $sentSms->patients()->attach($patient->id);
            $completedAt = (clone $sentAt)->addMinutes(rand(1, 5));
            $completedSms = SmsMessage::factory()->create([
                'content' => $message,
                'status' => 'completed',
                'sent_at' => $completedAt,
            ]);
            $completedSms->patients()->attach($patient->id);
            $patient->update([
                'status' => PatientStatusHelper::getStatus($patient),
                'last_sent_at' => $completedAt,
            ]);
        }

        // Sent only
        foreach ($sentPatients as $patient) {
            $appointment = $patient->appointment_at ?? now()->setTime(11, 0, 0);
            $patient->update(['appointment_at' => $appointment]);
            $offset = rand(10, 20);
            $sentAt = (clone $appointment)->subMinutes($offset);
            $sentSms = SmsMessage::factory()->create([
                'content' => $message,
                'status' => 'sent',
                'sent_at' => $sentAt,
            ]);
            $sentSms->patients()->attach($patient->id);
            $patient->update([
                'status' => PatientStatusHelper::getStatus($patient),
                'last_sent_at' => $sentAt,
            ]);
        }

        // Pending only
        foreach ($pendingPatients as $patient) {
            $appointment = $patient->appointment_at ?? now()->setTime(11, 0, 0);
            $patient->update(['appointment_at' => $appointment]);
            $pendingSms = SmsMessage::factory()->create([
                'content' => $message,
                'status' => 'pending',
                'sent_at' => $appointment,
            ]);
            $pendingSms->patients()->attach($patient->id);
            $patient->update([
                'status' => PatientStatusHelper::getStatus($patient),
                'last_sent_at' => null,
            ]);
        }

        // Ensure at least one of each status exists in the database
        $statuses = ['pending', 'sent', 'completed', 'failed'];
        foreach ($statuses as $status) {
            if (SmsMessage::where('status', $status)->count() === 0) {
                $sms = SmsMessage::factory()->create([
                    'content' => $message,
                    'status' => $status,
                    'sent_at' => now(),
                ]);
                $randomPatient = Patient::inRandomOrder()->first();
                $sms->patients()->attach($randomPatient->id);
            }
        }
    }
}
