<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Patient;

class DemoPatientSeeder extends Seeder
{
    /**
     * Seed demo patients for a given user.
     */
    public static function seedForUser(User $user, int $count = 100): void
    {
        \Database\Seeders\PatientSeeder::seedForUser($user, $count);
        \Database\Seeders\SmsMessageSeeder::seedForUser($user);
    }

    public function run(): void
    {
        // Not used directly; use seedForUser
    }
} 