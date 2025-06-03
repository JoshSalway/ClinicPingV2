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
    expect(SmsMessage::where('status', 'pending')->count())->toBeGreaterThan(0);
    expect(SmsMessage::whereDate('sent_at', Carbon::today('UTC'))->where('status', 'pending')->count())->toBeGreaterThan(0);
    expect(Patient::whereDate('appointment_at', Carbon::today('UTC'))->count())->toBeGreaterThan(0);
}); 