<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testRegisterSuccess(): void
    {
        $this->post('/api/users/', [
            'name' => 'Fakhrul',
            'phone' => '08888888888',
            'email' => 'fakhrul@mail.com',
            'password' => '12345678'
        ])->assertStatus(201)
        ->assertJson([
            'data' => [
                'name' => 'Fakhrul',
                'phone' => '08888888888',
                'email' => 'fakhrul@mail.com',
            ]
        ]);
    }
    
    public function testRegisterFailed(): void
    {
        $this->post('/api/users/', [
            'name' => '',
            'phone' => '',
            'email' => '',
            'password' => 'a'
        ])->assertStatus(400)
        ->assertJson([
            'errors' => [
                'name' => [
                    
                ],
                'phone' => [
                    'The phone field is required.'
                ],
                'email' => [
                    'The email field is required.'
                ],
                'password' => [
                    'The password field must be at least 8 characters.'
                ],
            ]
        ]);
    }
    
    public function testRegisterEmailOrPhoneAlreadyExist(): void
    {
        $this->testRegisterSuccess();
        $this->post('/api/users/', [
            'name' => 'Fakhrul',
            'phone' => '08888888888',
            'email' => 'fakhrul@mail.com',
            'password' => '12345678'
        ])->assertStatus(400)
        ->assertJson([
            'errors' => [
                'phone' => [
                    'phone already registered'
                ],
                'email' => [
                    'email already registered'
                ],
            ]
        ]);
    }

    public function testLoginSuccessWithPhone(): void
    {
        $this->seed([UserSeeder::class]);
        $this->post('/api/users/login', [
            'email_or_phone' => '0888888888',
            'password' => '12345678'
        ])->assertStatus(200)
        ->assertJson([
            'data' => [
                'name' => 'test',
                'phone' => '0888888888',
                'email' => 'test@mail.com',
            ]
        ]);

        $user = User::where('email', 'test@mail.com')->first();
        self::assertNotNull($user->token);
    }

    public function testLoginSuccessWithEmail(): void
    {
        $this->seed([UserSeeder::class]);
        $this->post('/api/users/login', [
            'email_or_phone' => 'test@mail.com',
            'password' => '12345678'
        ])->assertStatus(200)
        ->assertJson([
            'data' => [
                'name' => 'test',
                'phone' => '0888888888',
                'email' => 'test@mail.com',
            ]
        ]);

        $user = User::where('email', 'test@mail.com')->first();
        self::assertNotNull($user->token);
    }
    
    public function testLoginFailedPhoneOrEmailNotFound(): void
    {
        $this->seed([UserSeeder::class]);
        $this->post('/api/users/login', [
            'email_or_phone' => 'amin@mail.com',
            'password' => '12345678'
        ])->assertStatus(401)
        ->assertJson([
            'errors' => [
                'message' => [
                    'phone or email or password wrong'
                ]
            ]
        ]);
    }

    public function testLoginFailedPasswordWrong(): void
    {
        $this->seed([UserSeeder::class]);
        $this->post('/api/users/login', [
            'email_or_phone' => 'mail@mail.com',
            'password' => '123456789'
        ])->assertStatus(401)
        ->assertJson([
            'errors' => [
                'message' => [
                    'phone or email or password wrong'
                ]
            ]
        ]);
    }

    public function testGetSuccess(): void
    {
        $this->seed([UserSeeder::class]);

        $this->get('api/users/current', [
            'Authorization' => 'test'
        ])->assertStatus(200)
        ->assertJson([
            'data' => [
                'name' => 'test',
                'phone' => '0888888888',
                'email' => 'test@mail.com',
            ]
        ]);
    }

    public function testGetUnauthorized(): void
    {
        $this->seed([UserSeeder::class]);

        $this->get('api/users/current')
        ->assertStatus(401)
        ->assertJson([
            'errors' => [
                'message' => [
                    'Unauthorized'
                ]
            ]
        ]);
    }
    
    public function testGetInvalidToken(): void
    {
        $this->seed([UserSeeder::class]);

        $this->get('api/users/current', [
            'Authorization' => 'invalid_token'
        ])->assertStatus(401)
        ->assertJson([
            'errors' => [
                'message' => [
                    'Unauthorized'
                ]
            ]
        ]);
    }

    public function testUpdatePasswordSuccess(): void
    {
        $this->seed([UserSeeder::class]);
        $oldUser = User::where('email', 'test@mail.com')->first();

        $this->patch('api/users/current', 
            [
                'password' => 'password_changed'
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(200)
        ->assertJson([
            'data' => [
                'name' => 'test',
                'phone' => '0888888888',
                'email' => 'test@mail.com',
            ]
        ]);
        $newUser = User::where('email', 'test@mail.com')->first();
        self::assertNotEquals($oldUser->password, $newUser->password);
    }

    public function testUpdateNameSuccess(): void
    {
        $this->seed([UserSeeder::class]);
        $oldUser = User::where('email', 'test@mail.com')->first();

        $this->patch('api/users/current', 
            [
                'name' => 'amin'
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(200)
        ->assertJson([
            'data' => [
                'name' => 'amin',
                'phone' => '0888888888',
                'email' => 'test@mail.com',
            ]
        ]);
        $newUser = User::where('email', 'test@mail.com')->first();
        self::assertNotEquals($oldUser->name, $newUser->name);
        
    }

    public function testUpdatePhoneSuccess(): void
    {
        $this->seed([UserSeeder::class]);
        $oldUser = User::where('email', 'test@mail.com')->first();

        $this->patch('api/users/current', 
            [
                'phone' => '08987654321'
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(200)
        ->assertJson([
            'data' => [
                'name' => 'test',
                'phone' => '08987654321',
                'email' => 'test@mail.com',
            ]
        ]);
        $newUser = User::where('email', 'test@mail.com')->first();
        self::assertNotEquals($oldUser->phone, $newUser->phone);
        
    }

    public function testUpdatePhoneFailed(): void
    {
        $this->seed([UserSeeder::class]);

        $this->patch('api/users/current', 
            [
                'phone' => '08123456789'
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(400)
        ->assertJson([
            'errors' => [
                'phone' => [
                    'phone already registered'
                ]
            ]
        ]);
    }

    public function testUpdateFailed(): void
    {
        $this->seed([UserSeeder::class]);

        $this->patch('api/users/current', 
            [
                'name' => 'AminAminAminAminAminAminAminAminAminAminAminAminAminAminAminAminAminAminAminAminAminAminAminAminAmin'
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(400)
        ->assertJson([
            'errors' => [
                'name' => [
                    'The name field must not be greater than 50 characters.'
                ]
            ]
        ]);
        
    }
    
}
