<?php

use function Pest\Laravel\postJson;
use function Pest\Laravel\artisan;
use App\Models\Patient;
use App\Models\SmsMessage;
use Illuminate\Support\Carbon;

beforeEach(function () {
    artisan('migrate:fresh');
    // Ensure demo mode for tests
    config(['services.sms_mode' => 'demo']);
});

test('can send sms in demo mode and update patient', function () {
    $patient = Patient::factory()->create([
        'status' => 'pending',
        'last_sent_at' => null,
        'phone' => '+61 412 345 678',
    ]);
    $message = 'Test SMS message';

    $response = postJson('/api/sms/send', [
        'patient_id' => $patient->id,
        'message' => $message,
    ]);

    $response->assertOk()->assertJson(['success' => true]);

    $patient->refresh();
    expect($patient->status)->toBe('sent');
    expect($patient->last_sent_at)->not->toBeNull();

    $sms = SmsMessage::where('content', $message)->first();
    expect($sms)->not->toBeNull();
    expect($sms->status)->toBe('sent');
    expect($sms->patients->pluck('id'))->toContain($patient->id);
});

test('returns error if patient does not exist', function () {
    $response = postJson('/api/sms/send', [
        'patient_id' => 999999,
        'message' => 'Hello',
    ]);
    $response->assertStatus(422);
});

test('returns error if message is missing', function () {
    $patient = Patient::factory()->create();
    $response = postJson('/api/sms/send', [
        'patient_id' => $patient->id,
    ]);
    $response->assertStatus(422);
}); 