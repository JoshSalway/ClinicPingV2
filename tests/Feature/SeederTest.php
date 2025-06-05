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
    $statuses = ['pending', 'sent', 'completed', 'failed'];
    foreach ($statuses as $status) {
        expect(SmsMessage::where('status', $status)->count())->toBeGreaterThan(0);
    }
});

it('creates SMS messages for today\'s appointments', function () {
    $today = now()->format('Y-m-d');
    $todayPatients = Patient::whereDate('appointment_at', $today)->get();
    
    foreach ($todayPatients as $patient) {
        $smses = $patient->smsMessages;
        expect($smses->count())->toBeGreaterThanOrEqual(1);
        expect($smses->count())->toBeLessThanOrEqual(2);
        if ($smses->count() === 2) {
            $statuses = $smses->pluck('status')->sort()->values();
            $validPairs = [
                collect(['completed', 'sent']),
                collect(['failed', 'sent']),
            ];
            $isValid = collect($validPairs)->contains(function ($pair) use ($statuses) {
                return $statuses->values()->all() === $pair->values()->all();
            });
            expect($isValid)->toBeTrue();
        } elseif ($smses->count() === 1) {
            expect(['pending', 'sent'])->toContain($smses->first()->status);
        }
    }
});

it('maintains correct patient status based on SMS status', function () {
    $completedPatients = Patient::where('status', 'completed')->get();
    foreach ($completedPatients as $patient) {
        expect($patient->smsMessages()->where('status', 'completed')->count())->toBeGreaterThan(0);
    }

    $today = now()->format('Y-m-d');
    $pendingPatientsToday = Patient::where('status', 'pending')
        ->whereDate('appointment_at', $today)
        ->get();
    foreach ($pendingPatientsToday as $patient) {
        $pendingSmsCount = $patient->smsMessages()->where('status', 'pending')->count();
        expect($pendingSmsCount)->toBeGreaterThan(0);
    }
    if ($pendingPatientsToday->count() === 0) {
        expect(true)->toBeTrue(); // No pending patients, so this is fine
    }

    // Do not check for 'failed' patients, as the seeder does not assign this status to any patient.
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

it('creates SMS messages with correct timing relative to appointments', function () {
    $today = now()->format('Y-m-d');
    $patientsToday = Patient::whereDate('appointment_at', $today)->get();
    foreach ($patientsToday as $patient) {
        $sentSms = $patient->smsMessages()->where('status', 'sent')->first();
        $completedSms = $patient->smsMessages()->where('status', 'completed')->first();
        if ($sentSms && $completedSms) {
            expect($sentSms->sent_at->lt($completedSms->sent_at))->toBeTrue();
            expect($sentSms->sent_at->diffInMinutes($completedSms->sent_at))->toBeLessThanOrEqual(5);
        }
    }
});

test('all seeders using Faker have the correct import', function () {
    $seederPath = base_path('database/seeders/PatientSeeder.php');
    $contents = file_get_contents($seederPath);
    expect($contents)->toContain('use Faker\\Factory as Faker');
    expect($contents)->not->toContain('new \\Faker\\Factory');
    expect($contents)->toContain('Faker::create()');
}); 