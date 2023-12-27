<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Outlet;
use App\Models\User;
use Database\Seeders\EmployeeListSeeder;
use Database\Seeders\EmployeeOutletSeeder;
use Database\Seeders\EmployeeSeeder;
use Database\Seeders\OutletListSeeder;
use Database\Seeders\OutletSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testCreateSuccess()
    {
        $user = User::factory()->create([
            'name' => 'test',
            'email' => "test@mail.com",
            'phone' => "08123456789",
            'password' => Hash::make('password123'),
        ]);

        $employee = Employee::factory()->create([
            
        ])

        $access_token = $user->createToken('auth_token')->plainTextToken;

        $this->seed([OutletListSeeder::class]);
        $outlet = Outlet::query()->limit(2)->get();
        $response = $this->post('/api/employees', [
            'name' => 'test',
            'phone' => '08123456789',
            'pin' => '1234',
            'email' => '',
            'role' => '1',
            'outletIds' => [$outlet[0]->id, $outlet[1]->id]
        ], [
            'Authorization' => 'Bearer ' . $access_token
        ])->assertStatus(201)
        ->assertJson([
            'data' => [
                'name' => 'test',
                'phone' => '08123456789',
                'email' => '',
                'role' => 'Kasir',
                'outlets' => array()
            ]
        ])->json();

        // test that there are 2 outlets
        self::assertEquals(2, count($response['data']['outlets']));

        return $access_token;
    }

    public function testCreateFailed(): void
    {
        $user = User::factory()->create([
            'name' => 'test',
            'email' => "test@mail.com",
            'phone' => "08123456789",
            'password' => Hash::make('password123'),
        ]);

        $access_token = $user->createToken('auth_token')->plainTextToken;
        $this->seed([OutletSeeder::class]);
        $outlet = Outlet::query()->limit(1)->first();
        $this->post('/api/employees', [
            'name' => '',
            'phone' => '08123456789',
            'pin' => 'asdbd',
            'email' => '',
            'role' => 'Kasir',
            'outletIds' => [$outlet->id]
        ], [
            'Authorization' => 'Bearer ' . $access_token
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

        $user->tokens()->delete();
    }

    public function testCreateUnauthorized(): void
    {
        $user = User::factory()->create([
            'name' => 'test',
            'email' => "test@mail.com",
            'phone' => "08123456789",
            'password' => Hash::make('password123'),
        ]);

        $access_token = $user->createToken('auth_token')->plainTextToken;

        $this->seed([OutletSeeder::class]);
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

        $user->tokens()->delete();
    }

    public function testGetSuccess(): void
    {
        $access_token = $this->testCreateSuccess();
        $employee = Employee::query()->limit(1)->first();
        
        $this->get('api/employees/'. $employee->id, [
            'Authorization' => 'Bearer ' . $access_token
        ])->assertStatus(200)
        ->assertJson([
            'data' => [
                'name' => 'test',
                'phone' => '08123456789',
                'email' => '',
                'role' => 'Kasir',
                'outlets' => array()
            ]
        ]);
    }
    
    public function testGetEmployeeFailedNotFound(): void
    {
        $access_token = $this->testCreateSuccess();
        $employee = Employee::query()->limit(1)->first();
        
        $this->get('api/employees/'. ($employee->id + 1), [
            'Authorization' => 'Bearer ' . $access_token
        ])->assertStatus(404)
        ->assertJson([
            'errors' => [
                'message' => [
                    'Employee not found'
                ]
            ]
        ]);        
    }

    public function testGetEmployeeFailedUnauthorized(): void
    {
        $user = User::factory()->create([
            'name' => 'test',
            'email' => "test@mail.com",
            'phone' => "08123456789",
            'password' => Hash::make('password123'),
        ]);

        $access_token = $user->createToken('auth_token')->plainTextToken;

        $this->seed([EmployeeSeeder::class]);
        $employee = Employee::query()->limit(1)->first();
        
        $this->get('api/employees/'. $employee->id, [
            'Authorization' => 'salah token'
        ])->assertStatus(401)
        ->assertJson([
            'errors' => [
                'message' => [
                    'Unauthorized'
                ]
            ]
        ]);     
        
        $user->tokens()->delete();
    }

    public function testUpdateEmployeeSuccess(): void
    {
        $access_token = $this->testCreateSuccess();
        $oldEmployee = Employee::with('outlets')->limit(1)->first();
        $outlet = Outlet::query()->orderBy('id', 'desc')->limit(2)->get();
        
        $response = $this->put('api/employees/'. $oldEmployee->id, [
            'name' => 'test test',
            'phone' => '08123456789',
            'pin' => '1234',
            'email' => '',
            'role' => 'Manajer',
            'outletIds' => [$outlet[0]->id, $outlet[1]->id]
        ], [
            'Authorization' => 'Bearer ' . $access_token
        ])->assertStatus(200)
        ->assertJson([
            'data' => [
                'name' => 'test test',
                'phone' => '08123456789',
                'email' => '',
                'role' => 'Manajer',
                'outlets' => array()
            ]
        ])->json();

        $newEmployee = Employee::with('outlets')->limit(1)->first();
        self::assertNotEquals($oldEmployee->name, $newEmployee->name);
        self::assertNotEquals($oldEmployee->outlets, $newEmployee->outlets);
    }

    public function testUpdateEmployeeFailedValidation(): void
    {
        $user = User::factory()->create([
            'name' => 'test',
            'email' => "test@mail.com",
            'phone' => "08123456789",
            'password' => Hash::make('password123'),
        ]);

        $access_token = $user->createToken('auth_token')->plainTextToken;

        $this->seed([EmployeeSeeder::class, OutletListSeeder::class]);
        $employee = Employee::query()->limit(1)->first();
        $outlet = Outlet::query()->orderBy('id', 'desc')->limit(2)->get();

        $this->put('api/employees/'. ($employee->id + 1), [
            'name' => '',
            'phone' => '08123456789',
            'pin' => '1234',
            'email' => 'email',
            'role' => 2,
            'outletIds' => [$outlet[0]->id, $outlet[1]->id]
        ], [
            'Authorization' => 'Bearer ' . $access_token
        ])->assertStatus(400)
        ->assertJson([
            'errors' => [
                'name' => [
                    'The name field is required.'
                ],
                'email' => [
                    'The email field must be a valid email address.'
                ]
            ]
        ]);
    }
    
    public function testUpdateEmployeeFailedNotFound(): void
    {
        $user = User::factory()->create([
            'name' => 'test',
            'email' => "test@mail.com",
            'phone' => "08123456789",
            'password' => Hash::make('password123'),
        ]);

        $access_token = $user->createToken('auth_token')->plainTextToken;

        $this->seed([EmployeeSeeder::class, OutletListSeeder::class]);
        $employee = Employee::query()->limit(1)->first();
        $outlet = Outlet::query()->orderBy('id', 'desc')->limit(2)->get();

        $this->put('api/employees/'. ($employee->id + 1), [
            'name' => 'test test',
            'phone' => '08123456789',
            'pin' => '1234',
            'email' => '',
            'role' => 2,
            'outletIds' => [$outlet[0]->id, $outlet[1]->id]
        ], [
            'Authorization' => 'Bearer ' . $access_token
        ])->assertStatus(404)
        ->assertJson([
            'errors' => [
                'message' => [
                    'Employee not found'
                ]
            ]
        ]);
    }
    
    public function testUpdateEmployeeFailedUnauthorized(): void
    {
        $user = User::factory()->create([
            'name' => 'test',
            'email' => "test@mail.com",
            'phone' => "08123456789",
            'password' => Hash::make('password123'),
        ]);

        $access_token = $user->createToken('auth_token')->plainTextToken;

        $this->seed([EmployeeSeeder::class, OutletListSeeder::class]);
        $employee = Employee::query()->limit(1)->first();
        $outlet = Outlet::query()->orderBy('id', 'desc')->limit(2)->get();
        
        $this->put('api/employees/'. ($employee->id + 1), [
            'name' => 'test test',
            'phone' => '08123456789',
            'pin' => '1234',
            'email' => '',
            'role' => 2,
            'outletIds' => [$outlet[0]->id, $outlet[1]->id]
        ], [
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

    public function testDeleteSuccess(): void
    {
        $access_token = $this->testCreateSuccess();
        $employee = Employee::query()->limit(1)->first();

        $this->delete('api/employees/'.$employee->id, [], [
            'Authorization' => 'Bearer ' . $access_token
        ])->assertStatus(200)
        ->assertJson([
            'data' => true
        ]);
    }

    public function testDeleteNotFound(): void
    {
        $access_token = $this->testCreateSuccess();
        $employee = Employee::query()->limit(1)->first();

        $this->delete('api/employees/'.($employee->id+1), [], [
            'Authorization' => 'Bearer ' . $access_token
        ])->assertStatus(404)
        ->assertJson([
            'errors' => [
                'message' => [
                    'Employee not found'
                ]
            ] 
        ]);
    }

    public function testSearchByNameSuccess(): void
    {
        $user = User::factory()->create([
            'name' => 'test',
            'email' => "test@mail.com",
            'phone' => "08123456789",
            'password' => Hash::make('password123'),
        ]);

        $access_token = $user->createToken('auth_token')->plainTextToken;
        
        $this->seed([EmployeeListSeeder::class]);
        $response = $this->get('/api/employees?name=employee 1', [
            'Authorization' => 'Bearer ' . $access_token
        ])->assertStatus(200)->json();
        self::assertEquals(1, count($response['data']));

        Log::info(json_encode($response, JSON_PRETTY_PRINT));
    }

    public function testSearchByOutletIdSuccess(): void
    {
        $user = User::factory()->create([
            'name' => 'test',
            'email' => "test@mail.com",
            'phone' => "08123456789",
            'password' => Hash::make('password123'),
        ]);

        $access_token = $user->createToken('auth_token')->plainTextToken;

        $this->seed([EmployeeListSeeder::class, OutletListSeeder::class, EmployeeOutletSeeder::class]);
        $outlet = Outlet::query()->limit(1)->first();

        $response = $this->get('/api/employees?outletid='.$outlet->id, [
            'Authorization' => 'Bearer ' . $access_token
        ])->assertStatus(200)->json();

        // Log::info(json_encode($response, JSON_PRETTY_PRINT));
    }

    public function testSearchByOutletIAndNameSuccess(): void
    {
        $user = User::factory()->create([
            'name' => 'test',
            'email' => "test@mail.com",
            'phone' => "08123456789",
            'password' => Hash::make('password123'),
        ]);

        $access_token = $user->createToken('auth_token')->plainTextToken;

        $this->seed([EmployeeListSeeder::class, OutletListSeeder::class, EmployeeOutletSeeder::class]);
        $outlet = Outlet::query()->limit(1)->first();

        $response = $this->get('/api/employees?name=1&outletid='.$outlet->id, [
            'Authorization' => 'Bearer ' . $access_token
        ])->assertStatus(200)->json();

        // Log::info(json_encode($response, JSON_PRETTY_PRINT));
    }
    
    public function testGetAllSuccess(): void
    {
        $user = User::factory()->create([
            'name' => 'test',
            'email' => "test@mail.com",
            'phone' => "08123456789",
            'password' => Hash::make('password123'),
        ]);

        $access_token = $user->createToken('auth_token')->plainTextToken;
        $this->seed([EmployeeListSeeder::class]);
        $response = $this->get('/api/employees', [
            'Authorization' => 'Bearer ' . $access_token
        ])->assertStatus(200)->json();
        self::assertEquals(5, count($response['data']));

        // Log::info($response);
    }
    
    public function testGetAllEmpty(): void
    {
        $user = User::factory()->create([
            'name' => 'test',
            'email' => "test@mail.com",
            'phone' => "08123456789",
            'password' => Hash::make('password123'),
        ]);

        $access_token = $user->createToken('auth_token')->plainTextToken;
        // $this->seed([EmployeeListSeeder::class]);
        $response = $this->get('/api/employees', [
            'Authorization' => 'Bearer ' . $access_token
        ])->assertStatus(200)->json();
        self::assertEquals(0, count($response['data']));
        self::assertEmpty($response['data']);
    }

    public function testGetAllUnauthorized(): void
    {
        $user = User::factory()->create([
            'name' => 'test',
            'email' => "test@mail.com",
            'phone' => "08123456789",
            'password' => Hash::make('password123'),
        ]);

        $access_token = $user->createToken('auth_token')->plainTextToken;
        $this->seed([EmployeeListSeeder::class]);
        $this->get('/api/employees', [
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
