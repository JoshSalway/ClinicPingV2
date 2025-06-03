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
        $now = now();

        // Get 20 random patients with status 'sent'
        $smsPatients = Patient::where('status', 'sent')->inRandomOrder()->take(20)->get();
        $completedCount = 18;
        $failedCount = 2;
        $completedPatients = $smsPatients->slice(0, $completedCount);
        $failedPatients = $smsPatients->slice($completedCount, $failedCount);

        foreach ($completedPatients as $patient) {
            // Appointment at 11:00 AM today
            $appointment = $now->copy()->setTime(11, 0, 0);
            $patient->update(['appointment_at' => $appointment]);
            // Sent at 10:45 AM
            $sentAt = $appointment->copy()->subMinutes(15);
            $sentSms = SmsMessage::create([
                'content' => $message,
                'status' => 'sent',
                'sent_at' => $sentAt,
            ]);
            $sentSms->patients()->syncWithoutDetaching([$patient->id]);
            // Completed at 10:46 AM
            $completedAt = $sentAt->copy()->addMinute();
            $completedSms = SmsMessage::create([
                'content' => $message,
                'status' => 'completed',
                'sent_at' => $completedAt,
            ]);
            $completedSms->patients()->syncWithoutDetaching([$patient->id]);
            $patient->update(['status' => 'completed', 'last_sent_at' => $completedAt]);
        }

        foreach ($failedPatients as $patient) {
            // Appointment at 11:00 AM today
            $appointment = $now->copy()->setTime(11, 0, 0);
            $patient->update(['appointment_at' => $appointment]);
            // Sent at 10:45 AM
            $sentAt = $appointment->copy()->subMinutes(15);
            $sentSms = SmsMessage::create([
                'content' => $message,
                'status' => 'sent',
                'sent_at' => $sentAt,
            ]);
            $sentSms->patients()->syncWithoutDetaching([$patient->id]);
            // Failed at 10:46 AM
            $failedAt = $sentAt->copy()->addMinute();
            $failedSms = SmsMessage::create([
                'content' => $message,
                'status' => 'failed',
                'sent_at' => $failedAt,
            ]);
            $failedSms->patients()->syncWithoutDetaching([$patient->id]);
            $patient->update(['status' => 'failed', 'last_sent_at' => $failedAt]);
        }
    }
}
