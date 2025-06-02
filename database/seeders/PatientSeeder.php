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

        // Helper to generate phone numbers by country
        function randomPhone($faker, $country) {
            if ($country === 'AU') {
                return '+61 4' . $faker->numberBetween(10, 99) . ' ' . $faker->numberBetween(100, 999) . ' ' . $faker->numberBetween(100, 999);
            } elseif ($country === 'CO') {
                return '+57 3' . $faker->numberBetween(10, 99) . ' ' . $faker->numberBetween(100, 999) . ' ' . $faker->numberBetween(1000, 9999);
            } else {
                return '+1 ' . $faker->numberBetween(200, 999) . '-' . $faker->numberBetween(200, 999) . '-' . $faker->numberBetween(1000, 9999);
            }
        }

        $auCount = (int)round($total * 0.45);
        $coCount = (int)round($total * 0.45);
        $usCount = $total - $auCount - $coCount;
        $countryPool = array_merge(
            array_fill(0, $auCount, 'AU'),
            array_fill(0, $coCount, 'CO'),
            array_fill(0, $usCount, 'US')
        );
        shuffle($countryPool);

        for ($i = 0; $i < $total; $i++) {
            $country = array_pop($countryPool);
            Patient::factory()->create([
                'appointment_at' => $faker->optional()->dateTimeBetween('-2 weeks', '+2 weeks'),
                'phone' => randomPhone($faker, $country),
            ]);
        }
    }
} 