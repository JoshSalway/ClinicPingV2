<?php

namespace Database\Seeders;

use App\Models\Patient;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    public function run(): void
    {
        // Console log the seeder is running
        \Log::info('PatientSeeder is running');

        $faker = \Faker\Factory::create();
        $total = 100;
        $patients = collect();
        $today = now()->format('Y-m-d');

        // Randomly select 20 indices for patients who will have SMS sent
        $smsIndices = collect(range(0, $total - 1))->shuffle()->take(20)->values();
        // Randomly select 15-20 indices for today's appointments
        $apptTodayCount = rand(15, 20);
        $apptTodayIndices = collect(range(0, $total - 1))->shuffle()->take($apptTodayCount)->values();

        for ($i = 0; $i < $total; $i++) {
            $country = 'AU';
            $isSms = $smsIndices->contains($i);
            $isToday = $apptTodayIndices->contains($i);
            $status = $isSms ? 'sent' : 'pending'; // Default, will be updated by SmsMessageSeeder
            $appointment = $isToday ? $today : $faker->optional()->dateTimeBetween('-2 weeks', '+2 weeks');
            $patients->push(Patient::factory()->create([
                'appointment_at' => $appointment,
                'phone' => $this->randomPhone($faker, $country),
                'status' => $status,
            ]));
        }
    }

    private function randomPhone($faker, $country) {
        if ($country === 'AU') {
            return '+61 4' . $faker->numberBetween(10, 99) . ' ' . $faker->numberBetween(100, 999) . ' ' . $faker->numberBetween(100, 999);
        } elseif ($country === 'CO') {
            return '+57 3' . $faker->numberBetween(10, 99) . ' ' . $faker->numberBetween(100, 999) . ' ' . $faker->numberBetween(1000, 9999);
        } else {
            return '+1 ' . $faker->numberBetween(200, 999) . '-' . $faker->numberBetween(200, 999) . '-' . $faker->numberBetween(1000, 9999);
        }
    }
} 