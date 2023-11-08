<?php

namespace Tests\Feature;

use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OutletTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testCreateSuccess(): void
    {
        $this->seed([UserSeeder::class]);
        $this->post('/api/outlets', [
            'name' => 'test restoran',
            'address' => 'yogyakarta',
            'phone' => '08123456789',
            'email' => 'restoran@gmail.com',
            'is_active' => true
        ], [
            'Authorization' => 'test'
        ])->assertStatus(201)
        ->assertJson([
            'data' => [
                'name' => 'test restoran',
                'address' => 'yogyakarta',
                'phone' => '08123456789',
                'email' => 'restoran@gmail.com',
                'is_active' => true
            ]
        ]);
    }

    public function testCreateFailed(): void
    {
        $this->seed([UserSeeder::class]);

        $this->post('/api/outlets', [
            'name' => 'test restoran',
            'address' => 'yogyakarta',
            'phone' => '08123456789',
            'email' => 'restoran.com',
            'is_active' => true
        ], [
            'Authorization' => 'test'
        ])->assertStatus(400)
        ->assertJson([
            'errors' => [
                'email' => [
                    'The email field must be a valid email address.'
                ]
            ]
        ]);
    }

    public function testCreateUnauthorized(): void
    {
        $this->seed([UserSeeder::class]);

        $this->post('/api/outlets', [
            'name' => 'test restoran',
            'address' => 'yogyakarta',
            'phone' => '08123456789',
            'email' => 'restoran.com',
            'is_active' => true
        ], [
            'Authorization' => ''
        ])->assertStatus(401)
        ->assertJson([
            'errors' => [
                'message' => [
                    'Unauthorized'
                ]
            ]
        ]);
    }
}
