<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Patient;
use App\Models\SmsMessage;

uses(RefreshDatabase::class);

it('prevents duplicate patient-sms relationships', function () {
    $patient = Patient::factory()->create();
    $sms = SmsMessage::factory()->create();

    $patient->smsMessages()->syncWithoutDetaching([$sms->id]);
    $patient->smsMessages()->syncWithoutDetaching([$sms->id]);

    expect($patient->smsMessages()->count())->toBe(1);
}); 