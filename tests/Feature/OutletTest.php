<?php

namespace Tests\Feature;

use App\Models\Outlet;
use Database\Seeders\OutletListSeeder;
use Database\Seeders\OutletSeeder;
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
            'email' => 'restoran@mail.com',
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

    public function testGetSuccess(): void
    {
        $this->seed([UserSeeder::class, OutletSeeder::class]);
        $outlet = Outlet::query()->limit(1)->first();

        $this->get('api/outlets/'.$outlet->id, [
            'Authorization' => 'test'
        ])->assertStatus(200)
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

    public function testGetNotFound(): void
    {
        $this->seed([UserSeeder::class, OutletSeeder::class]);
        $outlet = Outlet::query()->limit(1)->first();

        $this->get('api/outlets/'.($outlet->id + 1), [
            'Authorization' => 'test'
        ])->assertStatus(404)
        ->assertJson([
            'errors' => [
                'message' => [
                    'Outlet not found'
                ]
            ]
        ]);
    }

    public function testGetOtherUserOutlet(): void
    {
        $this->seed([UserSeeder::class, OutletSeeder::class]);
        $outlet = Outlet::query()->limit(1)->first();

        $this->get('api/outlets/'.$outlet->id, [
            'Authorization' => 'test2'
        ])->assertStatus(404)
        ->assertJson([
            'errors' => [
                'message' => [
                    'Outlet not found'
                ]
            ]
        ]);
    }

    public function testUpdateSuccess(): void
    {
        $this->seed([UserSeeder::class, OutletSeeder::class]);
        $outlet = Outlet::query()->limit(1)->first();

        $this->put('api/outlets/'.$outlet->id, [
            'name' => 'test restoran 2',
            'address' => 'yogyakarta 2',
            'phone' => '081234567892',
            'email' => 'restoran2@gmail.com',
            'is_active' => true
        ], [
            'Authorization' => 'test'
        ])->assertStatus(200)
        ->assertJson([
            'data' => [
                'name' => 'test restoran 2',
                'address' => 'yogyakarta 2',
                'phone' => '081234567892',
                'email' => 'restoran2@gmail.com',
                'is_active' => true
            ]
        ]);
    }

    public function testUpdateValidationError(): void
    {
        $this->seed([UserSeeder::class, OutletSeeder::class]);
        $outlet = Outlet::query()->limit(1)->first();

        $this->put('api/outlets/'.$outlet->id, [
            'name' => '',
            'address' => 'yogyakarta 2',
            'phone' => '081234567892',
            'email' => 'restoran2gmail.com',
            'is_active' => 'true'
        ], [
            'Authorization' => 'test'
        ])->assertStatus(400)
        ->assertJson([
            'errors' => [
                'name' => [
                    'The name field is required.'
                ],
                'email' => [
                    'The email field must be a valid email address.'
                ],
                'is_active' => [
                    'The is active field must be true or false.'
                ]
            ]
        ]);
    }

    public function testDeleteSuccess(): void
    {
        $this->seed([UserSeeder::class, OutletSeeder::class]);
        $outlet = Outlet::query()->limit(1)->first();

        $this->delete('api/outlets/'.$outlet->id, [], [
            'Authorization' => 'test'
        ])->assertStatus(200)
        ->assertJson([
            'data' => true
        ]);
    }

    public function testDeleteNotFound(): void
    {
        $this->seed([UserSeeder::class, OutletSeeder::class]);
        $outlet = Outlet::query()->limit(1)->first();

        $this->delete('api/outlets/'.($outlet->id+1), [], [
            'Authorization' => 'test'
        ])->assertStatus(404)
        ->assertJson([
            'errors' => [
                'message' => [
                    'Outlet not found'
                ]
            ] 
        ]);
    }

    public function testGetAllSuccess(): void
    {
        $this->seed([UserSeeder::class, OutletListSeeder::class]);

        $response = $this->get('api/outlets', [
            'Authorization' => 'test'
        ])->assertStatus(200)->json();

        self::assertEquals(10, count($response['data']));

    }

}
