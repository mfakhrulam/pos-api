<?php

namespace Tests\Feature;

use App\Models\Customer;
use Database\Seeders\CustomerListSeeder;
use Database\Seeders\CustomerSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testCreateSuccess(): void
    {
        $this->seed([UserSeeder::class]);
        $this->post('/api/customers', [
            'name' => 'test cust',
            'phone' => '08123456789',
            'email' => '',
            'gender' => '1',
        ], [
            'Authorization' => 'test'
        ])->assertStatus(201)
        ->assertJson([
            'data' => [
                'name' => 'test cust',
                'phone' => '08123456789',
                'email' => '',
                'gender' => 'Laki-laki',
            ]
        ]);
    }

    public function testCreateFailed(): void
    {
        $this->seed([UserSeeder::class]);
        $this->post('/api/customers', [
            'name' => 'test cust test custtest custtest custtest custtest custtest custtest cust',
            'phone' => '',
            'email' => 'email',
            'gender' => 1,
        ], [
            'Authorization' => 'test'
        ])->assertStatus(400)
        ->assertJson([
            'errors' => [
                'name' => [
                    'The name field must not be greater than 50 characters.'
                ],
                'phone' => [
                    'The phone field is required.'
                ],
                'email' => [
                    'The email field must be a valid email address.'
                ]
            ]
        ]);
    }
    
    public function testCreateUnauthorized(): void
    {
        $this->seed([UserSeeder::class]);
        $this->post('/api/customers', [
            'name' => 'test cust',
            'phone' => '08123456789',
            'email' => 'email',
            'gender' => 1,
        ], [
            'Authorization' => 'token salah'
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
        $this->seed([UserSeeder::class, CustomerSeeder::class]);
        $customer = Customer::query()->limit(1)->first();

        $this->get('api/customers/' . $customer->id, [
            'Authorization' => 'test'
        ])->assertStatus(200)
        ->assertJson([
            'data' => [
                'name' => 'test cust',
                'phone' => '08123456789',
                'email' => '',
                'gender' => 'Laki-laki',
            ]
        ]);
    }
    
    public function testGetNotFound(): void
    {
        $this->seed([UserSeeder::class, CustomerSeeder::class]);
        $customer = Customer::query()->limit(1)->first();

        $this->get('api/customers/'.($customer->id + 1), [
            'Authorization' => 'test'
        ])->assertStatus(404)
        ->assertJson([
            'errors' => [
                'message' => [
                    'Customer not found'
                ]
            ]
        ]);
    }

    public function testGetOtherUserCustomer(): void
    {
        $this->seed([UserSeeder::class, CustomerSeeder::class]);
        $customer = Customer::query()->limit(1)->first();

        $this->get('api/customers/'.$customer->id, [
            'Authorization' => 'test2'
        ])->assertStatus(404)
        ->assertJson([
            'errors' => [
                'message' => [
                    'Customer not found'
                ]
            ]
        ]);
    }

    public function testGetUnauthorized(): void
    {
        $this->seed([UserSeeder::class, CustomerSeeder::class]);
        $customer = Customer::query()->limit(1)->first();

        $this->get('api/customers/'.$customer->id, [
            'Authorization' => 'salah token'
        ])->assertStatus(401)
        ->assertJson([
            'errors' => [
                'message' => [
                    'Unauthorized'
                ]
            ]
        ]);
    }

    public function testUpdateSuccess(): void
    {
        $this->seed([UserSeeder::class, CustomerSeeder::class]);
        $customer = Customer::query()->limit(1)->first();

        $this->put('api/customers/' . $customer->id, [
            'name' => 'test cust',
            'phone' => '08123456789',
            'email' => '',
            'gender' => 'Perempuan',
        ], [
            'Authorization' => 'test'
        ])->assertStatus(200)
        ->assertJson([
            'data' => [
                'name' => 'test cust',
                'phone' => '08123456789',
                'email' => '',
                'gender' => 'Perempuan',
            ]
        ]);
    }

    public function testUpdateValidationError(): void
    {
        $this->seed([UserSeeder::class, CustomerSeeder::class]);
        $customer = Customer::query()->limit(1)->first();

        $this->put('api/customers/' . $customer->id, [
            'name' => 'test cust',
            'phone' => '',
            'email' => '',
            'gender' => '',
        ], [
            'Authorization' => 'test'
        ])->assertStatus(400)
        ->assertJson([
            'errors' => [
                'phone' => [
                    'The phone field is required.'
                ],
                'gender' => [
                    'The gender field is required.'
                ]
            ]
        ]);
    }

    public function testUpdateUnauthorized(): void
    {
        $this->seed([UserSeeder::class, CustomerSeeder::class]);
        $customer = Customer::query()->limit(1)->first();

        $this->put('api/customers/' . $customer->id, [
            'name' => 'test cust',
            'phone' => '08123456789',
            'email' => '',
            'gender' => 'Perempuan',
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

    public function testUpdateNotFound(): void
    {
        $this->seed([UserSeeder::class, CustomerSeeder::class]);
        $customer = Customer::query()->limit(1)->first();

        $this->put('api/customers/' . ($customer->id + 1), [
            'name' => 'test cust',
            'phone' => '08123456789',
            'email' => '',
            'gender' => 'Perempuan',
        ], [
            'Authorization' => 'test'
        ])->assertStatus(404)
        ->assertJson([
            'errors' => [
                'message' => [
                    'Customer not found'
                ]
            ]
        ]);
    }

    public function testDeleteSuccess(): void
    {
        $this->seed([UserSeeder::class, CustomerSeeder::class]);
        $customer = Customer::query()->limit(1)->first();

        $this->delete('api/customers/' . $customer->id, [], [
            'Authorization' => 'test'
        ])->assertStatus(200)
        ->assertJson([
            'data' => true
        ]);
    }

    public function testDeleteNotFound(): void
    {
        $this->seed([UserSeeder::class, CustomerSeeder::class]);
        $customer = Customer::query()->limit(1)->first();

        $this->delete('api/customers/' . ($customer->id+1), [], [
            'Authorization' => 'test'
        ])->assertStatus(404)
        ->assertJson([
            'errors' => [
                'message' => [
                    'Customer not found'
                ]
            ] 
        ]);
    }

    public function testSearchByNameSuccess(): void
    {
        $this->seed([UserSeeder::class, CustomerListSeeder::class]);
        $response = $this->get('/api/customers?name=test cust', [
            'Authorization' => 'test'
        ])->assertStatus(200)->json();
        self::assertEquals(5, count($response['data']));

        Log::info(json_encode($response, JSON_PRETTY_PRINT));
    }

    public function testGetAllSuccess(): void
    {
        $this->seed([UserSeeder::class, CustomerListSeeder::class]);
        $response = $this->get('/api/customers', [
            'Authorization' => 'test'
        ])->assertStatus(200)->json();
        self::assertEquals(5, count($response['data']));

        // Log::info(encode_json($response, JSON_PRETTY_PRINT));
    }
    
    public function testGetAllEmpty(): void
    {
        $this->seed([UserSeeder::class, CustomerListSeeder::class]);
        $response = $this->get('/api/customers', [
            'Authorization' => 'test2'
        ])->assertStatus(200)->json();
        self::assertEquals(0, count($response['data']));
        self::assertEmpty($response['data']);
    }

    public function testGetAllUnauthorized(): void
    {
        $this->seed([UserSeeder::class, CustomerListSeeder::class]);
        $this->get('/api/customers', [
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
