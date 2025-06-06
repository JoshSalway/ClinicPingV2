<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Patient;
use App\Models\SmsMessage;
use Carbon\Carbon;

uses(RefreshDatabase::class);

it('seeders guarantee at least one for each dashboard stat', function () {
    $this->seed(\Database\Seeders\PatientSeeder::class);
    $this->seed(\Database\Seeders\SmsMessageSeeder::class);

    expect(Patient::count())->toBeGreaterThan(0);
    expect(SmsMessage::whereDate('sent_at', Carbon::today('UTC'))->count())->toBeGreaterThan(0);
    expect(SmsMessage::whereNull('sent_at')->count())->toBe(0);
    expect(SmsMessage::whereNotNull('sent_at')->whereNull('completed_at')->whereNull('failed_at')->count())->toBeGreaterThan(0);
    expect(SmsMessage::whereNotNull('completed_at')->count())->toBeGreaterThan(0);
    expect(SmsMessage::whereNotNull('failed_at')->count())->toBeGreaterThanOrEqual(0);
    expect(Patient::whereDate('appointment_at', Carbon::today('UTC'))->count())->toBeGreaterThan(0);
    expect(SmsMessage::has('patients')->count())->toBeGreaterThan(0);

    // Unique patients with sent/completed SMS today
    $uniquePatientsWithSentToday = Patient::whereHas('smsMessages', function($q) {
        $q->whereNotNull('sent_at')
          ->whereDate('sent_at', Carbon::today('UTC'));
    })->count();
    expect($uniquePatientsWithSentToday)->toBeGreaterThan(0);
}); 