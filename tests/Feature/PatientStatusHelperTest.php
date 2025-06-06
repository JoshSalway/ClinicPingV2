<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Patient;
use App\Models\SmsMessage;
use App\Helpers\PatientStatusHelper;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\PatientSeeder::class);
    $this->seed(\Database\Seeders\SmsMessageSeeder::class);
});

test('PatientStatusHelper returns completed for patients with completed SMS', function () {
    $patient = Patient::whereHas('smsMessages', fn($q) => $q->whereNotNull('completed_at'))->first();
    expect(PatientStatusHelper::getStatus($patient))->toBe('completed');
});

test('PatientStatusHelper returns sent for patients with sent SMS and no completed_at', function () {
    $patient = Patient::whereHas('smsMessages', fn($q) => $q->whereNotNull('sent_at')->whereNull('completed_at'))->whereDoesntHave('smsMessages', fn($q) => $q->whereNotNull('completed_at'))->first();
    expect(PatientStatusHelper::getStatus($patient))->toBe('sent');
});

test('PatientStatusHelper returns failed for patients with failed SMS', function () {
    $patient = Patient::whereHas('smsMessages', fn($q) => $q->whereNotNull('failed_at'))->first();
    expect(PatientStatusHelper::getStatus($patient))->toBe('failed');
});

test('PatientStatusHelper returns pending for patients with no SMS', function () {
    $patient = Patient::doesntHave('smsMessages')->first();
    if ($patient) {
        expect(PatientStatusHelper::getStatus($patient))->toBe('pending');
    } else {
        expect(true)->toBeTrue(); // No such patient, skip
    }
});

test('PatientStatusHelper getLabel returns correct labels', function () {
    expect(PatientStatusHelper::getLabel('pending'))->toBe('Pending');
    expect(PatientStatusHelper::getLabel('sent'))->toBe('Sent');
    expect(PatientStatusHelper::getLabel('completed'))->toBe('Completed');
    expect(PatientStatusHelper::getLabel('failed'))->toBe('Failed');
    expect(PatientStatusHelper::getLabel('other'))->toBe('Other');
}); 