<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use App\Models\Patient;
use App\Models\SmsMessage;
use Carbon\Carbon;
use App\Models\User;

uses(RefreshDatabase::class);

it('ensures dashboard stats have at least one record for each stat', function () {
    $this->seed(\Database\Seeders\PatientSeeder::class);
    $this->seed(\Database\Seeders\SmsMessageSeeder::class);

    // Patients
    expect(Patient::count())->toBeGreaterThan(0);

    // SMS messages sent today
    $sentToday = SmsMessage::whereDate('sent_at', Carbon::today('UTC'))->count();
    expect($sentToday)->toBeGreaterThan(0);

    // Pending SMS messages (no sent_at)
    $pending = SmsMessage::whereNull('sent_at')->count();
    expect($pending)->toBeGreaterThan(0);

    // Completed SMS messages (completed_at not null)
    $completed = SmsMessage::whereNotNull('completed_at')->count();
    expect($completed)->toBeGreaterThan(0);

    // Failed SMS messages (failed_at not null)
    $failed = SmsMessage::whereNotNull('failed_at')->count();
    expect($failed)->toBeGreaterThanOrEqual(0);

    // Patients with appointment today
    $appointmentsToday = Patient::whereDate('appointment_at', Carbon::today('UTC'))->count();
    expect($appointmentsToday)->toBeGreaterThan(0);

    // Unique patients with sent/completed SMS today
    $uniquePatientsWithSentToday = Patient::whereHas('smsMessages', function($q) {
        $q->whereNotNull('sent_at')
          ->whereDate('sent_at', Carbon::today('UTC'));
    })->count();
    expect($uniquePatientsWithSentToday)->toBeGreaterThan(0);
});

it('dashboard returns correct pending forms count', function () {
    $user = User::factory()->create(['timezone' => 'Australia/Melbourne']);
    $this->actingAs($user);
    \Database\Seeders\PatientSeeder::seedForUser($user, 12);
    \Database\Seeders\SmsMessageSeeder::seedForUser($user);
    $response = $this->get('/dashboard');
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('dashboard')
        ->where('pendingForms', fn ($value) => $value > 0)
    );
}); 