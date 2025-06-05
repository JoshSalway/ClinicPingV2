<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Demo data is only seeded for individual users on registration or reset.
        // Do NOT uncomment the lines below unless you want to seed demo data for ALL users globally (not recommended for MVP/demo):
        // $this->call([
        //     PatientSeeder::class,
        //     SmsMessageSeeder::class,
        // ]);
    }
}
