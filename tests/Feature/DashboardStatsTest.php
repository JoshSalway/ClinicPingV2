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

    // Pending SMS messages
    $pending = SmsMessage::where('status', 'pending')->count();
    expect($pending)->toBeGreaterThan(0);

    // Patients with appointment today
    $appointmentsToday = Patient::whereDate('appointment_at', Carbon::today('UTC'))->count();
    expect($appointmentsToday)->toBeGreaterThan(0);
});

it('dashboard returns correct pending forms count', function () {
    $this->seed(\Database\Seeders\PatientSeeder::class);
    $this->seed(\Database\Seeders\SmsMessageSeeder::class);

    $this->actingAs(User::factory()->create());
    $response = $this->get('/dashboard');
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('dashboard')
        ->where('pendingForms', fn ($value) => $value > 0)
    );
}); 