<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Patient;
use App\Models\SmsMessage;

uses(RefreshDatabase::class);

it('prevents duplicate patient-sms relationships', function () {
    $patient = Patient::factory()->create();
    $sms = SmsMessage::factory()->create();
    $sms->patients()->attach($patient->id);
    $sms->patients()->syncWithoutDetaching($patient->id);

    expect($patient->smsMessagesMany()->count())->toBe(1);
    expect($sms->patients->pluck('id'))->toContain($patient->id);
}); 