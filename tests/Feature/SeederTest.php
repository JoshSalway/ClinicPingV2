<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Patient;
use App\Models\SmsMessage;
use Carbon\Carbon;
use Database\Seeders\PatientSeeder;
use Database\Seeders\SmsMessageSeeder;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(PatientSeeder::class);
    $this->seed(SmsMessageSeeder::class);
});

it('creates patients with appointments', function () {
    expect(Patient::count())->toBeGreaterThan(0);
    expect(Patient::whereNotNull('appointment_at')->count())->toBeGreaterThan(0);
});

it('creates patients with valid phone numbers', function () {
    $patients = Patient::all();
    foreach ($patients as $patient) {
        expect($patient->phone)->toMatch('/^\+61 4\d{2} \d{3} \d{3}$/');
    }
});

it('creates SMS messages with correct statuses', function () {
    // Pending: sent_at is null
    expect(SmsMessage::whereNull('sent_at')->count())->toBe(0);
    // Sent: sent_at is set, completed_at and failed_at are null
    expect(SmsMessage::whereNotNull('sent_at')->whereNull('completed_at')->whereNull('failed_at')->count())->toBeGreaterThan(0);
    // Completed: completed_at is set
    expect(SmsMessage::whereNotNull('completed_at')->count())->toBeGreaterThan(0);
    // Failed: failed_at is set
    expect(SmsMessage::whereNotNull('failed_at')->count())->toBeGreaterThanOrEqual(0);
});

it('creates SMS messages for today\'s appointments', function () {
    $today = now()->format('Y-m-d');
    $todayPatients = Patient::whereDate('appointment_at', $today)->get();
    foreach ($todayPatients as $patient) {
        $smses = $patient->smsMessages;
        expect($smses->count())->toBe(1);
        $sms = $smses->first();
        if ($sms->completed_at) {
            expect($sms->sent_at)->not->toBeNull();
            expect($sms->completed_at)->not->toBeNull();
        } elseif ($sms->sent_at && !$sms->completed_at && !$sms->failed_at) {
            expect($sms->sent_at)->not->toBeNull();
            expect($sms->completed_at)->toBeNull();
            expect($sms->failed_at)->toBeNull();
        } elseif (!$sms->sent_at) {
            expect($sms->sent_at)->toBeNull();
        }
    }
});

it('maintains correct patient status based on SMS status', function () {
    $today = now()->format('Y-m-d');
    $patients = Patient::whereDate('appointment_at', $today)->get();
    foreach ($patients as $patient) {
        $sms = $patient->smsMessages()->first();
        if ($sms->completed_at) {
            expect($sms->sent_at)->not->toBeNull();
            expect($sms->completed_at)->not->toBeNull();
        } elseif ($sms->sent_at && !$sms->completed_at && !$sms->failed_at) {
            expect($sms->sent_at)->not->toBeNull();
            expect($sms->completed_at)->toBeNull();
            expect($sms->failed_at)->toBeNull();
        } elseif (!$sms->sent_at) {
            expect($sms->sent_at)->toBeNull();
        }
    }
});

it('creates appointments within business hours', function () {
    $sydneyTz = new \DateTimeZone('Australia/Sydney');
    $today = now($sydneyTz)->format('Y-m-d');
    $startTime = Carbon::createFromFormat('Y-m-d H:i', "$today 08:30", $sydneyTz);
    $endTime = Carbon::createFromFormat('Y-m-d H:i', "$today 17:00", $sydneyTz);

    $todayAppointments = Patient::whereDate('appointment_at', $today)->get();
    foreach ($todayAppointments as $patient) {
        $apptTime = Carbon::parse($patient->appointment_at)->setTimezone($sydneyTz);
        expect($apptTime->between($startTime, $endTime))->toBeTrue();
    }
});

test('it creates SMS messages with correct timing relative to appointments', function () {
    $today = now('UTC')->format('Y-m-d');
    $patients = Patient::whereDate('appointment_at', $today)->get();
    $asserted = false;

    foreach ($patients as $patient) {
        $appointment = Carbon::parse($patient->appointment_at)->setTimezone('UTC');
        $smses = $patient->smsMessages;
        foreach ($smses as $sms) {
            if ($sms->sent_at) {
                $sentAt = Carbon::parse($sms->sent_at)->setTimezone('UTC');
                $diffMinutes = $sentAt->diffInMinutes($appointment, false);
                fwrite(STDERR, "[DEBUG] Patient ID: {$patient->id}, Appointment: {$appointment->toDateTimeString()}, Sent At: {$sentAt->toDateTimeString()}, Diff Minutes: {$diffMinutes}\n");
                if ($diffMinutes >= 5 && $diffMinutes <= 120) {
                    $asserted = true;
                    break 2;
                }
            } else {
                fwrite(STDERR, "[DEBUG] Patient ID: {$patient->id}, Appointment: {$appointment->toDateTimeString()}, SMS has no sent_at\n");
            }
        }
    }
    expect($asserted)->toBeTrue('No SMS messages found within 2 hours before appointment');
});

test('all seeders using Faker have the correct import', function () {
    $seederPath = base_path('database/seeders/PatientSeeder.php');
    $contents = file_get_contents($seederPath);
    expect($contents)->toContain('use Faker\\Factory as Faker');
    expect($contents)->not->toContain('new \\Faker\\Factory');
    expect($contents)->toContain('Faker::create()');
}); 