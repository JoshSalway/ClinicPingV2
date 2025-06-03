<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Patient;
use App\Models\SmsMessage;

uses(RefreshDatabase::class);

it('can create a patient and relate sms messages', function () {
    $patient = Patient::factory()->create();
    $sms = SmsMessage::factory()->create();

    $patient->smsMessages()->attach($sms->id);

    expect($patient->smsMessages)->toHaveCount(1);
    expect($sms->patients)->toHaveCount(1);
}); 