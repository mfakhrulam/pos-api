<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Outlet;
use Database\Seeders\EmployeeSeeder;
use Database\Seeders\OutletListSeeder;
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
        $this->seed([UserSeeder::class, OutletListSeeder::class]);
        $outlet = Outlet::query()->limit(2)->get();
        $response = $this->post('/api/employees', [
            'name' => 'test',
            'phone' => '08123456789',
            'pin' => '1234',
            'email' => '',
            'role' => '1',
            'outletIds' => [$outlet[0]->id, $outlet[1]->id]
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
        ])->json();

        // test that there are 2 outlets
        self::assertEquals(2, count($response['data']['outlets']));
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

    public function testGetSuccess(): void
    {
        $this->testCreateSuccess();
        $employee = Employee::query()->limit(1)->first();
        
        $this->get('api/employees/'. $employee->id, [
            'Authorization' => 'test'
        ])->assertStatus(200)
        ->assertJson([
            'data' => [
                'name' => 'test',
                'phone' => '08123456789',
                'email' => '',
                'role' => 'Kasir',
                'outlets' => array()
            ]
        ])->json();
    }
    
    public function testGetEmployeeNotFound(): void
    {
        $this->testCreateSuccess();
        $employee = Employee::query()->limit(1)->first();
        
        $this->get('api/employees/'. ($employee->id + 1), [
            'Authorization' => 'test'
        ])->assertStatus(404)
        ->assertJson([
            'errors' => [
                'message' => [
                    'Employee not found'
                ]
            ]
        ]);        
    }

    public function testGetEmployeeUnauthorized(): void
    {
        $this->testCreateSuccess();
        $employee = Employee::query()->limit(1)->first();
        
        $this->get('api/employees/'. ($employee->id + 1), [
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

    public function testGetOtherUserEmployee(): void
    {
        $this->seed([UserSeeder::class, EmployeeSeeder::class]);
        $employee = Employee::query()->limit(1)->first();

        $this->get('api/employees/'.$employee->id, [
            'Authorization' => 'test2'
        ])->assertStatus(404)
        ->assertJson([
            'errors' => [
                'message' => [
                    'Employee not found'
                ]
            ]
        ]);
    }
}
