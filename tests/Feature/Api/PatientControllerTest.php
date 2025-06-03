<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('patient phone validation accepts valid international numbers', function () {
    $response = $this->postJson('/api/patients', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'phone' => '+61412345678', // AU
        'email' => 'john@example.com',
    ]);
    $response->assertStatus(201);

    $response = $this->postJson('/api/patients', [
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'phone' => '+15551234567', // US
        'email' => 'jane@example.com',
    ]);
    $response->assertStatus(201);
});

test('patient phone validation rejects invalid numbers', function () {
    $response = $this->postJson('/api/patients', [
        'first_name' => 'Bad',
        'last_name' => 'Number',
        'phone' => '0412345678', // Missing +61
        'email' => 'bad@example.com',
    ]);
    $response->assertStatus(422);

    $response = $this->postJson('/api/patients', [
        'first_name' => 'Bad',
        'last_name' => 'Number',
        'phone' => '+123', // Too short
        'email' => 'bad2@example.com',
    ]);
    $response->assertStatus(422);

    $response = $this->postJson('/api/patients', [
        'first_name' => 'Bad',
        'last_name' => 'Number',
        'phone' => '+61 4xx xxx xxx', // Not digits
        'email' => 'bad3@example.com',
    ]);
    $response->assertStatus(422);
}); 