<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Patient;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('seeds demo patients for new users and isolates patients per user', function () {
    // Register user1
    $user1 = User::factory()->create([
        'email' => 'user1@example.com',
        'password' => Hash::make('password1'),
    ]);
    \Database\Seeders\DemoPatientSeeder::seedForUser($user1);
    $this->actingAs($user1);
    expect(Patient::where('user_id', $user1->id)->count())->toBeGreaterThan(0);
    $user1PatientIds = Patient::where('user_id', $user1->id)->pluck('id')->toArray();

    // Register user2
    $user2 = User::factory()->create([
        'email' => 'user2@example.com',
        'password' => Hash::make('password2'),
    ]);
    \Database\Seeders\DemoPatientSeeder::seedForUser($user2);
    $this->actingAs($user2);
    expect(Patient::where('user_id', $user2->id)->count())->toBeGreaterThan(0);
    $user2PatientIds = Patient::where('user_id', $user2->id)->pluck('id')->toArray();

    // Each user only sees their own patients
    $this->actingAs($user1);
    $visiblePatients = Patient::where('user_id', auth()->id())->pluck('id')->toArray();
    expect($visiblePatients)->toEqualCanonicalizing($user1PatientIds);
    expect(array_intersect($user1PatientIds, $user2PatientIds))->toBe([]);

    $this->actingAs($user2);
    $visiblePatients = Patient::where('user_id', auth()->id())->pluck('id')->toArray();
    expect($visiblePatients)->toEqualCanonicalizing($user2PatientIds);
});

it('deletes all patients when a user is deleted and reseeds on re-signup', function () {
    $user = User::factory()->create([
        'email' => 'deleteuser@example.com',
        'password' => Hash::make('password'),
    ]);
    \Database\Seeders\DemoPatientSeeder::seedForUser($user);
    $this->actingAs($user);
    $patientCount = Patient::where('user_id', $user->id)->count();
    expect($patientCount)->toBeGreaterThan(0);

    // Delete user
    $user->delete();
    expect(Patient::where('user_id', $user->id)->count())->toBe(0);

    // Re-signup (simulate new user with same email)
    $user2 = User::factory()->create([
        'email' => 'deleteuser@example.com',
        'password' => Hash::make('password'),
    ]);
    \Database\Seeders\DemoPatientSeeder::seedForUser($user2);
    $this->actingAs($user2);
    $newPatientCount = Patient::where('user_id', $user2->id)->count();
    expect($newPatientCount)->toBeGreaterThan(0);
}); 