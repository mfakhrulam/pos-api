<?php

namespace Tests\Feature;

use App\Models\Outlet;
use Database\Seeders\OutletSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testCreateSuccess(): void
    {
        $this->seed([UserSeeder::class, OutletSeeder::class]);
        $outlet = Outlet::query()->limit(1)->first();
        $this->post('/api/employees', [
            'name' => 'test',
            'phone' => '08123456789',
            'pin' => '1234',
            'email' => '',
            'role' => '1',
            'outletIds' => [$outlet->id]
        ], [
            'Authorization' => 'test'
        ])->assertStatus(201)
        ->assertJson([
            'data' => [
                'name' => 'test',
                'phone' => '08123456789',
                'email' => '',
                'role' => '1',
                'outlets' => array()
            ]
        ]);
    }

    public function testCreateFailed(): void
    {
        $this->seed([UserSeeder::class, OutletSeeder::class]);
        $outlet = Outlet::query()->limit(1)->first();
        $this->post('/api/employees', [
            'name' => '',
            'phone' => '08123456789',
            'pin' => 'asdbd',
            'email' => '',
            'role' => '1',
            'outletIds' => [$outlet->id]
        ], [
            'Authorization' => 'test'
        ])->assertStatus(400)
        ->assertJson([
            'errors' => [
                'name' => [
                    'The name field is required.'
                ],
                'pin' => [
                    'The pin field must be 4 characters.',
                    'The pin field format is invalid.'
                ],
            ]
        ]);
    }

    public function testCreateUnauthorized(): void
    {
        
        $this->seed([UserSeeder::class, OutletSeeder::class]);
        $outlet = Outlet::query()->limit(1)->first();
        $this->post('/api/employees', [
            'name' => 'test',
            'phone' => '08123456789',
            'pin' => '1234',
            'email' => '',
            'role' => '1',
            'outletIds' => [$outlet->id]
        ], [
            'Authorization' => 'tokensalah'
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
