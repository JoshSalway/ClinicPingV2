<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Patient;
use App\Models\SmsMessage;
use Carbon\Carbon;

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

        // First 3: completed, rest: pending
        $completedPatients = $todaysPatients->take(3);
        $pendingPatients = $todaysPatients->slice(3);

        foreach ($completedPatients as $patient) {
            $appointment = $patient->appointment_at;
            if (!$appointment) {
                $appointment = now()->setTime(11, 0, 0);
                $patient->update(['appointment_at' => $appointment]);
            }
            $offset = rand(10, 20); // minutes before appointment
            $sentAt = (clone $appointment)->subMinutes($offset);
            $sentSms = SmsMessage::create([
                'content' => $message,
                'status' => 'sent',
                'sent_at' => $sentAt,
            ]);
            $sentSms->patients()->attach($patient->id);
            $completedAt = (clone $sentAt)->addMinutes(rand(1, 5));
            $completedSms = SmsMessage::create([
                'content' => $message,
                'status' => 'completed',
                'sent_at' => $completedAt,
            ]);
            $completedSms->patients()->attach($patient->id);
            $patient->update(['status' => 'completed', 'last_sent_at' => $completedAt]);
        }

        foreach ($pendingPatients as $patient) {
            // Create a pending SMS for today
            $pendingSms = SmsMessage::create([
                'content' => $message,
                'status' => 'pending',
                'sent_at' => Carbon::now(),
            ]);
            $pendingSms->patients()->attach($patient->id);
            $patient->update(['status' => 'pending', 'last_sent_at' => null]);
        }

        // Get 20 random patients with status 'sent'
        $smsPatients = Patient::where('status', 'sent')->inRandomOrder()->take(20)->get();
        $completedCount = 18;
        $failedCount = 2;
        $completedPatients = $smsPatients->slice(0, $completedCount);
        $failedPatients = $smsPatients->slice($completedCount, $failedCount);

        foreach ($completedPatients as $patient) {
            $appointment = $patient->appointment_at;
            if (!$appointment) {
                $appointment = now()->setTime(11, 0, 0);
                $patient->update(['appointment_at' => $appointment]);
            }
            $offset = rand(10, 20); // minutes before appointment
            $sentAt = (clone $appointment)->subMinutes($offset);
            $sentSms = SmsMessage::create([
                'content' => $message,
                'status' => 'sent',
                'sent_at' => $sentAt,
            ]);
            $sentSms->patients()->attach($patient->id);
            $completedAt = (clone $sentAt)->addMinutes(rand(1, 5));
            $completedSms = SmsMessage::create([
                'content' => $message,
                'status' => 'completed',
                'sent_at' => $completedAt,
            ]);
            $completedSms->patients()->attach($patient->id);
            $patient->update(['status' => 'completed', 'last_sent_at' => $completedAt]);
        }

        foreach ($failedPatients as $patient) {
            $appointment = $patient->appointment_at;
            if (!$appointment) {
                $appointment = now()->setTime(11, 0, 0);
                $patient->update(['appointment_at' => $appointment]);
            }
            $offset = rand(10, 20); // minutes before appointment
            $sentAt = (clone $appointment)->subMinutes($offset);
            $sentSms = SmsMessage::create([
                'content' => $message,
                'status' => 'sent',
                'sent_at' => $sentAt,
            ]);
            $sentSms->patients()->attach($patient->id);
            $failedAt = (clone $sentAt)->addMinutes(rand(1, 5));
            $failedSms = SmsMessage::create([
                'content' => $message,
                'status' => 'failed',
                'sent_at' => $failedAt,
            ]);
            $failedSms->patients()->attach($patient->id);
            $patient->update(['status' => 'failed', 'last_sent_at' => $failedAt]);
        }
    }
}
