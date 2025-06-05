<?php

namespace Database\Seeders;

use App\Models\Patient;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\User;
use Faker\Factory as FakerFactory;

class PatientSeeder extends Seeder
{
    public static function seedForUser(User $user, int $count = 20)
    {
        $seeder = new static();
        return $seeder->runForUser($user, $count);
    }

    public function runForUser(User $user, int $count = 100)
    {
        $total = $count;
        $patients = collect();
        $sydneyTz = new \DateTimeZone('Australia/Sydney');
        $todaySydneyDate = Carbon::now($sydneyTz)->format('Y-m-d');
        $rooms = 3;
        $startTime = Carbon::createFromFormat('Y-m-d H:i', "$todaySydneyDate 08:30", $sydneyTz);
        $endTime = Carbon::createFromFormat('Y-m-d H:i', "$todaySydneyDate 17:00", $sydneyTz);
        $breaks = [
            [Carbon::createFromFormat('Y-m-d H:i', "$todaySydneyDate 10:30", $sydneyTz), Carbon::createFromFormat('Y-m-d H:i', "$todaySydneyDate 11:00", $sydneyTz)],
            [Carbon::createFromFormat('Y-m-d H:i', "$todaySydneyDate 13:00", $sydneyTz), Carbon::createFromFormat('Y-m-d H:i', "$todaySydneyDate 14:00", $sydneyTz)],
        ];
        $todayAppointments = [];
        $apptTodayCount = min(12, $total); // Guarantee 12 for today

        // Build all possible 15-minute start slots between 08:30 and 17:00
        $possibleSlots = [];
        $cursor = clone $startTime;
        while ($cursor < $endTime) {
            // Skip breaks
            $inBreak = false;
            foreach ($breaks as [$breakStart, $breakEnd]) {
                if ($cursor->between($breakStart, $breakEnd, false)) {
                    $inBreak = true;
                    break;
                }
            }
            if (!$inBreak) {
                $possibleSlots[] = $cursor->copy();
            }
            $cursor->addMinutes(15);
        }

        // Shuffle and pick 12 unique slots
        shuffle($possibleSlots);
        $selectedSlots = array_slice($possibleSlots, 0, $apptTodayCount);

        foreach ($selectedSlots as $slot) {
            $todayAppointments[] = $slot->copy()->setTimezone('UTC');
        }

        // Ensure random order for realism
        shuffle($todayAppointments);

        // Randomly select indices for today's appointments
        $apptTodayIndices = collect(range(0, $total - 1))->shuffle()->take(count($todayAppointments))->values();

        for ($i = 0; $i < $total; $i++) {
            $isToday = $apptTodayIndices->contains($i);
            if ($isToday && !empty($todayAppointments)) {
                $appointment = array_pop($todayAppointments);
            } else {
                // Assign a random appointment NOT on today, rounded to nearest 15 min
                do {
                    $appointment = now()->addDays(rand(-14, 14));
                    $appointment = Carbon::instance($appointment)->minute((int)(round($appointment->format('i') / 15) * 15))->second(0);
                    $appointmentSydney = Carbon::instance($appointment)->setTimezone($sydneyTz);
                } while ($appointmentSydney->format('Y-m-d') === $todaySydneyDate);
            }

            // Final safety: Ensure all 'today' appointments are between 08:30 and 17:00
            if ($isToday && $appointment) {
                $apptSydney = Carbon::parse($appointment)->setTimezone($sydneyTz);
                $start = Carbon::createFromFormat('Y-m-d H:i', "$todaySydneyDate 08:30", $sydneyTz);
                $end = Carbon::createFromFormat('Y-m-d H:i', "$todaySydneyDate 17:00", $sydneyTz);
                if ($apptSydney->lt($start) || $apptSydney->gte($end)) {
                    $randMinutes = rand(0, (8.5 * 60));
                    $appointment = (clone $start)->addMinutes($randMinutes)->setTimezone('UTC');
                }
            }

            $patients->push(Patient::factory()->create([
                'appointment_at' => $appointment,
                'phone' => $this->randomPhone('AU'),
                'user_id' => $user->id,
            ]));
        }
        return $patients;
    }

    private function randomPhone($country) {
        $faker = FakerFactory::create();
        if ($country === 'AU') {
            return '+61 4' . $faker->numberBetween(10, 99) . ' ' . $faker->numberBetween(100, 999) . ' ' . $faker->numberBetween(100, 999);
        } elseif ($country === 'CO') {
            return '+57 3' . $faker->numberBetween(10, 99) . ' ' . $faker->numberBetween(100, 999) . ' ' . $faker->numberBetween(1000, 9999);
        } else {
            return '+1 ' . $faker->numberBetween(200, 999) . '-' . $faker->numberBetween(200, 999) . '-' . $faker->numberBetween(1000, 9999);
        }
    }

    public function run()
    {
        // For test compatibility: seed for the first user or create one
        $user = \App\Models\User::first() ?? \App\Models\User::factory()->create();
        $this->runForUser($user, 100);
    }
}