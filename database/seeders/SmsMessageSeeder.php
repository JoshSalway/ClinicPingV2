<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Patient;
use App\Models\SmsMessage;
use Carbon\Carbon;

class SmsMessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $patients = Patient::all();
        SmsMessage::factory(50)->create()->each(function ($sms) use ($patients) {
            // Attach to 1-3 random patients
            $sms->patients()->attach($patients->random(rand(1, 3))->pluck('id')->toArray());
        });

        // Ensure at least 1 form sent today (not pending)
        SmsMessage::factory()->state([
            'content' => 'Sent today only',
            'sent_at' => Carbon::today('UTC')->format('Y-m-d 00:00:00'),
            'status' => 'sent',
        ])->create()->each(function ($sms) use ($patients) {
            $sms->patients()->syncWithoutDetaching($patients->random(rand(1, min(3, $patients->count())))->pluck('id')->toArray());
        });

        // Ensure at least 1 pending form (not sent today)
        SmsMessage::factory()->state([
            'content' => 'Pending only',
            'sent_at' => Carbon::yesterday('UTC')->format('Y-m-d 00:00:00'),
            'status' => 'pending',
        ])->create()->each(function ($sms) use ($patients) {
            $sms->patients()->syncWithoutDetaching($patients->random(rand(1, min(3, $patients->count())))->pluck('id')->toArray());
        });

        // Ensure at least 1 form that is both sent today and pending
        SmsMessage::factory()->state([
            'content' => 'Sent today and pending',
            'sent_at' => Carbon::today('UTC')->format('Y-m-d 00:00:00'),
            'status' => 'pending',
        ])->create()->each(function ($sms) use ($patients) {
            $sms->patients()->syncWithoutDetaching($patients->random(rand(1, min(3, $patients->count())))->pluck('id')->toArray());
        });

        // Ensure at least 1 patient with today's appointment
        \App\Models\Patient::factory()->state([
            'appointment_at' => Carbon::today('UTC')->format('Y-m-d 00:00:00'),
        ])->create();

        // Minimal guaranteed SMS message for debugging
        $patient = $patients->first() ?? \App\Models\Patient::factory()->create();
        $sms = \App\Models\SmsMessage::create([
            'content' => 'Test SMS',
            'status' => 'pending',
            'sent_at' => Carbon::today('UTC')->format('Y-m-d 00:00:00'),
        ]);
        $sms->patients()->syncWithoutDetaching([$patient->id]);
    }
}
