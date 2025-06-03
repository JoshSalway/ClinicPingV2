<?php

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Database Statistics ===\n\n";

// Patients
echo "Patients:\n";
echo "Total Patients: " . \App\Models\Patient::count() . "\n";
echo "Patients with appointments today: " . \App\Models\Patient::whereDate('appointment_at', now())->count() . "\n";
echo "Patients by status:\n";
\App\Models\Patient::select('status', \DB::raw('count(*) as count'))
    ->groupBy('status')
    ->get()
    ->each(function($item) {
        echo "  {$item->status}: {$item->count}\n";
    });

echo "\nSMS Messages:\n";
echo "Total SMS Messages: " . \App\Models\SmsMessage::count() . "\n";
echo "SMS Messages sent today: " . \App\Models\SmsMessage::whereDate('sent_at', now())->count() . "\n";
echo "SMS Messages by status:\n";
\App\Models\SmsMessage::select('status', \DB::raw('count(*) as count'))
    ->groupBy('status')
    ->get()
    ->each(function($item) {
        echo "  {$item->status}: {$item->count}\n";
    });

echo "\nPatient-SMS Relationships:\n";
echo "Total relationships: " . \DB::table('patient_sms_message')->count() . "\n";
echo "Average SMS messages per patient: " . round(\DB::table('patient_sms_message')->count() / \App\Models\Patient::count(), 2) . "\n"; 