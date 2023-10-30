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
}
