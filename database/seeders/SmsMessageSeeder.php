<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Patient;
use App\Models\SmsMessage;
use Carbon\Carbon;
use App\Helpers\PatientStatusHelper;
use App\Models\User;

class SmsMessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $message = 'Please complete your medical history form: [form link]';
        $today = now()->format('Y-m-d');
        $tz = 'Australia/Melbourne'; // Default timezone

        // Get all patients with today's appointment, sorted by time
        $todaysPatients = Patient::whereDate('appointment_at', $today)
            ->orderBy('appointment_at')
            ->get();

        // Delete all SMS for today's patients to ensure idempotency
        foreach ($todaysPatients as $patient) {
            foreach ($patient->smsMessages as $sms) {
                $sms->patients()->detach($patient->id);
                // If SMS is not attached to any other patient, delete it
                if ($sms->patients()->count() === 0) {
                    $sms->delete();
                }
            }
        }

        // For today's appointments, assign statuses deterministically:
        // First 3: completed, next 3: sent, rest: pending
        $completedPatients = $todaysPatients->slice(0, 3);
        $sentPatients = $todaysPatients->slice(3, 3);
        $pendingPatients = $todaysPatients->slice(6);

        // Completed
        foreach ($completedPatients as $patient) {
            $appointment = $patient->appointment_at;
            $sentAt = Carbon::parse($appointment, $tz)->subMinutes(60)->setTimezone('UTC');
            $completedAt = $sentAt->copy()->addMinutes(10);
            $sms = SmsMessage::factory()->create([
                'content' => $message,
                'sent_at' => $sentAt,
                'completed_at' => $completedAt,
                'failed_at' => null,
            ]);
            $sms->patients()->attach($patient->id);
        }
        // Sent
        foreach ($sentPatients as $patient) {
            $appointment = $patient->appointment_at;
            $sentAt = Carbon::parse($appointment, $tz)->subMinutes(30)->setTimezone('UTC');
            $sms = SmsMessage::factory()->create([
                'content' => $message,
                'sent_at' => $sentAt,
                'completed_at' => null,
                'failed_at' => null,
            ]);
            $sms->patients()->attach($patient->id);
        }
        // Pending
        foreach ($pendingPatients as $patient) {
            $sms = SmsMessage::factory()->create([
                'content' => $message,
                'sent_at' => null,
                'completed_at' => null,
                'failed_at' => null,
            ]);
            $sms->patients()->attach($patient->id);
        }

        // Ensure at least one of each status exists in the database
        // Pending: no sent_at
        if (SmsMessage::whereNull('sent_at')->count() === 0) {
            $sms = SmsMessage::factory()->create([
                'content' => $message,
                'sent_at' => null,
                'completed_at' => null,
                'failed_at' => null,
            ]);
            $randomPatient = Patient::inRandomOrder()->first();
            $sms->patients()->attach($randomPatient->id);
        }
        // Sent: sent_at set, completed_at/failed_at null
        if (SmsMessage::whereNotNull('sent_at')->whereNull('completed_at')->whereNull('failed_at')->count() === 0) {
            $sms = SmsMessage::factory()->create([
                'content' => $message,
                'sent_at' => now(),
                'completed_at' => null,
                'failed_at' => null,
            ]);
            $randomPatient = Patient::inRandomOrder()->first();
            $sms->patients()->attach($randomPatient->id);
        }
        // Completed: sent_at and completed_at set
        if (SmsMessage::whereNotNull('sent_at')->whereNotNull('completed_at')->count() === 0) {
            $sentAt = now();
            $completedAt = $sentAt->copy()->addMinutes(5);
            $sms = SmsMessage::factory()->create([
                'content' => $message,
                'sent_at' => $sentAt,
                'completed_at' => $completedAt,
                'failed_at' => null,
            ]);
            $randomPatient = Patient::inRandomOrder()->first();
            $sms->patients()->attach($randomPatient->id);
        }
        // Failed: sent_at and failed_at set
        if (SmsMessage::whereNotNull('sent_at')->whereNotNull('failed_at')->count() === 0) {
            $sentAt = now();
            $failedAt = $sentAt->copy()->addMinutes(5);
            $sms = SmsMessage::factory()->create([
                'content' => $message,
                'sent_at' => $sentAt,
                'completed_at' => null,
                'failed_at' => $failedAt,
            ]);
            $randomPatient = Patient::inRandomOrder()->first();
            $sms->patients()->attach($randomPatient->id);
        }
    }

    public static function seedForUser(User $user)
    {
        $seeder = new static();
        $seeder->runForUser($user);
    }

    public function runForUser(User $user)
    {
        $message = 'Please complete your medical history form: [form link]';
        $tz = $user->timezone ?? 'Australia/Melbourne';
        $today = Carbon::now($tz)->format('Y-m-d');
        $todaysPatients = Patient::where('user_id', $user->id)
            ->whereDate('appointment_at', $today)
            ->orderBy('appointment_at')
            ->get();
        // Assign: 3 completed, 6 sent (3 of which are completed), rest pending
        $completedPatients = $todaysPatients->slice(0, 3);
        $sentPatients = $todaysPatients->slice(3, 6); // 6 sent, 3 of which will be completed
        $pendingPatients = $todaysPatients->slice(9);
        // Helper functions for clarity
        $createSentSms = function($patient, $appointment) use ($message, $tz) {
            $sentAt = Carbon::parse($appointment, $tz)->subMinutes(rand(10, 30))->setTimezone('UTC');
            $sms = SmsMessage::factory()->create([
                'content' => $message,
                'sent_at' => $sentAt,
                'completed_at' => null,
                'failed_at' => null,
            ]);
            $sms->patients()->attach($patient->id);
            return $sms;
        };
        $completeSms = function($sms, $afterTime) use ($tz) {
            if ($afterTime instanceof \Carbon\Carbon) {
                $after = $afterTime->copy();
            } elseif (is_string($afterTime)) {
                $after = \Carbon\Carbon::parse($afterTime, $tz);
            } elseif ($afterTime instanceof \DateTimeInterface) {
                $after = \Carbon\Carbon::instance($afterTime);
            } else {
                $after = \Carbon\Carbon::parse((string)$afterTime, $tz);
            }
            $completedAt = $after->addMinutes(rand(1, 10))->setTimezone('UTC');
            $sms->update([
                'completed_at' => $completedAt,
            ]);
            return $completedAt;
        };
        $createPendingSms = function($patient, $appointment) use ($message, $tz) {
            $pendingSms = SmsMessage::factory()->create([
                'content' => $message,
                'sent_at' => null,
                'completed_at' => null,
                'failed_at' => null,
            ]);
            $pendingSms->patients()->attach($patient->id);
        };
        // Completed: sent then completed (update same SMS)
        foreach ($completedPatients as $i => $patient) {
            $appointment = $patient->appointment_at;
            $sms = $createSentSms($patient, $appointment);
            $completedAt = $completeSms($sms, $sms->sent_at);
            // Debug: log sent_at and completed_at for first 3 patients
            if ($i < 3) {
                \Log::info('Seeder completed patient', [
                    'patient_id' => $patient->id,
                    'sent_at' => $sms->sent_at->toDateTimeString(),
                    'completed_at' => $sms->completed_at ? $sms->completed_at->toDateTimeString() : null,
                ]);
            }
        }
        // Sent only
        foreach ($sentPatients->slice(0, 3) as $patient) {
            $appointment = $patient->appointment_at;
            $sms = $createSentSms($patient, $appointment);
        }
        // Sent and completed
        foreach ($sentPatients->slice(3, 3) as $patient) {
            $appointment = $patient->appointment_at;
            $sms = $createSentSms($patient, $appointment);
            $completedAt = $completeSms($sms, $sms->sent_at);
        }
        // Pending only
        foreach ($pendingPatients as $patient) {
            $appointment = $patient->appointment_at;
            $createPendingSms($patient, $appointment);
        }
        // Assign one failed patient on another day
        $failedPatient = Patient::where('user_id', $user->id)
            ->whereDate('appointment_at', '!=', $today)
            ->inRandomOrder()
            ->first();
        if ($failedPatient) {
            $appointment = $failedPatient->appointment_at;
            $sms = $createSentSms($failedPatient, $appointment);
            $sentAt = $sms->sent_at;
            if ($sentAt instanceof \Carbon\Carbon) {
                $failedBase = $sentAt->copy();
            } elseif (is_string($sentAt)) {
                $failedBase = \Carbon\Carbon::parse($sentAt, $tz);
            } elseif ($sentAt instanceof \DateTimeInterface) {
                $failedBase = \Carbon\Carbon::instance($sentAt);
            } else {
                $failedBase = \Carbon\Carbon::parse((string)$sentAt, $tz);
            }
            $failedAt = $failedBase->addMinutes(rand(5, 15))->setTimezone('UTC');
            $failedSms = SmsMessage::factory()->create([
                'content' => $message,
                'sent_at' => $failedAt,
                'completed_at' => null,
                'failed_at' => $failedAt,
            ]);
            $failedSms->patients()->attach($failedPatient->id);
            // Debug: log sent_at and completed_at for failed patient
            \Log::info('Seeder failed patient', [
                'patient_id' => $failedPatient->id,
                'sent_at' => $sentAt->toDateTimeString(),
                'completed_at' => null,
            ]);
        }
        // Ensure at least one of each status exists in the database for this user
        $statuses = ['pending', 'sent', 'completed', 'failed'];
        foreach ($statuses as $status) {
            if (SmsMessage::where('status', $status)->whereHas('patients', function($q) use ($user) { $q->where('user_id', $user->id); })->count() === 0) {
                $sms = SmsMessage::factory()->create([
                    'content' => $message,
                    'sent_at' => Carbon::now($tz)->setTimezone('UTC'),
                ]);
                $randomPatient = Patient::where('user_id', $user->id)->inRandomOrder()->first();
                if ($randomPatient) {
                    $sms->patients()->attach($randomPatient->id);
                }
            }
        }
    }
}
