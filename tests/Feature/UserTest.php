<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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
            'phone' => '081234567898',
            'email' => 'fakhrul@mail.com',
            'password' => '12345678'
        ])->assertStatus(201)
        ->assertJson([
            'data' => [
                'name' => 'Fakhrul',
                'phone' => '081234567898',
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
            'phone' => '081234567898',
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

    public function testSendOTPSuccess()
    {
        $this->testRegisterSuccess();
        $response = $this->post('/api/users/send_otp', [
            'email' => 'fakhrul@mail.com'
        ])->assertStatus(200)->assertJsonStructure([
            'data' => [
                'otp',
                'token_expired_at'
            ]
        ]);
        
        return $response->json();
        // $user = User::factory()->make();
        // $response = $this->actingAs($user, 'api')
        // ->json('post', '/api/users/sendOtp', [
        //     'email' => 'user@gmail.com'
        // ]);
    }

    public function testSendOTPUserAlreadyVerifyEmail(): void
    {
        $user = User::factory()->create();
        $response = $this->post('/api/users/send_otp', [
            'email' => $user['email']
        ])->assertStatus(400)->assertJson([
            'errors' => [
                'message' => [
                    'The user has been verified, no OTP code required'
                ]
            ]
        ]);
        
    //     $response = $this->actingAs($user)
    //     ->json('post', '/api/users/send_otp', [
    //         'email' => 'user@gmail.com'
    //     ]);

    //     Log::info(json_decode($user, JSON_PRETTY_PRINT));
    }

    public function testVerifyOTPSuccess(): void
    {
        $OTPresource= $this->testSendOTPSuccess();
        $otp= $OTPresource['data']['otp'];
        $response = $this->post('/api/users/verify_otp', [
            'email' => 'fakhrul@mail.com', 
            'otp' => $otp
        ])->assertStatus(200)->assertJsonStructure([
            'data' => [
                'name',
                'phone',
                'email',
            ],
            'access_token',
            'token_type'
        ])->json();
    }

    public function testLoginSuccessWithPhone(): void
    {
        $user = User::factory()->create([
            'name' => 'test',
            'email' => "test@mail.com",
            'phone' => "08123456789",
            'password' => Hash::make('password123'),
        ]);

        $this->post('/api/users/login', [
            'email_or_phone' => $user->phone,
            'password' => 'password123'
        ])->assertStatus(200)
        ->assertJson([
            'data' => [
                'name' => 'test',
                'phone' => '08123456789',
                'email' => 'test@mail.com',
            ], 
            'token_type' => 'Bearer',
        ]);
    }

    public function testLoginSuccessWithEmail()
    {
        $user = User::factory()->create([
            'name' => 'test',
            'email' => "test@mail.com",
            'phone' => "08123456789",
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/api/users/login', [
            'email_or_phone' => $user->email,
            'password' => 'password123'
        ])->assertStatus(200)
        ->assertJson([
            'data' => [
                'name' => 'test',
                'phone' => '08123456789',
                'email' => 'test@mail.com',
            ], 
            'token_type' => 'Bearer',
        ]);
        Log::info(json_encode($response->json(), JSON_PRETTY_PRINT));

        return $response->json();
    }

    public function testLoginFailedEmailNotVerified()
    {
        $user = User::factory()->unverified()->create([
            'name' => 'test',
            'email' => "test@mail.com",
            'phone' => "08123456789",
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/api/users/login', [
            'email_or_phone' => $user->email,
            'password' => 'password123'
        ])->assertStatus(401)
        ->assertJson([
            'errors' => [
                'message' => [
                    'Email has not been verified'
                ]
            ]
        ]);
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
        $user = $this->testLoginSuccessWithEmail();

        $this->get('api/users/current', [
            'Authorization' => 'Bearer ' . $user['access_token']
        ])->assertStatus(200)
        ->assertJson([
            'data' => [
                'name' => 'test',
                'phone' => '08123456789',
                'email' => 'test@mail.com',
            ]
        ]);
    }

    public function testGetUnauthorized(): void
    {
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
        $user = $this->testLoginSuccessWithEmail();

        $this->get('api/users/current', [
            'Authorization' => 'Bearer ' . 'token salah'
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
        $user = $this->testLoginSuccessWithEmail();
        $oldUser = User::where('email', $user['data']['email'])->first();

        $this->patch('api/users/current', 
            [
                'password' => 'password_changed'
            ],
            [
                'Authorization' => 'Bearer ' . $user['access_token']
            ]
        )->assertStatus(200)
        ->assertJson([
            'data' => [
                'name' => 'test',
                'phone' => '08123456789',
                'email' => 'test@mail.com',
            ]
        ]);
        $newUser = User::where('email', $user['data']['email'])->first();
        self::assertNotEquals($oldUser->password, $newUser->password);
    }

    public function testUpdateNameSuccess(): void
    {
        $user = $this->testLoginSuccessWithEmail();
        $oldUser = User::where('email', $user['data']['email'])->first();

        $this->patch('api/users/current', 
            [
                'name' => 'amin'
            ],
            [
                'Authorization' => 'Bearer ' . $user['access_token']
            ]
        )->assertStatus(200)
        ->assertJson([
            'data' => [
                'name' => 'amin',
                'phone' => '08123456789',
                'email' => 'test@mail.com',
            ]
        ]);
        $newUser = User::where('email', $user['data']['email'])->first();
        self::assertNotEquals($oldUser->name, $newUser->name);
        
    }

    public function testUpdatePhoneSuccess(): void
    {
        $user = $this->testLoginSuccessWithEmail();
        $oldUser = User::where('email', $user['data']['email'])->first();

        $this->patch('api/users/current', 
            [
                'phone' => '088888888881'
            ],
            [
                'Authorization' => 'Bearer ' . $user['access_token']
            ]
        )->assertStatus(200)
        ->assertJson([
            'data' => [
                'name' => 'test',
                'phone' => '088888888881',
                'email' => 'test@mail.com',
            ]
        ]);
        $newUser = User::where('email', $user['data']['email'])->first();
        self::assertNotEquals($oldUser->phone, $newUser->phone);
        
    }

    public function testUpdatePhoneFailed(): void
    {
        $this->seed([UserSeeder::class]);
        $user = $this->testLoginSuccessWithEmail();

        $this->patch('api/users/current', 
            [
                'phone' => '0888888888'
            ],
            [
                'Authorization' => 'Bearer ' . $user['access_token']
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
        $user = $this->testLoginSuccessWithEmail();

        $this->patch('api/users/current', 
            [
                'name' => 'AminAminAminAminAminAminAminAminAminAminAminAminAminAminAminAminAminAminAminAminAminAminAminAminAmin'
            ],
            [
                'Authorization' => 'Bearer ' . $user['access_token']
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

    public function testLogoutSuccess(): void
    {
        $user = $this->testLoginSuccessWithEmail();

        $this->delete('api/users/logout', headers:[
                'Authorization' => 'Bearer ' . $user['access_token']
            ]
        )->assertStatus(200)
        ->assertJson([
            'data' => true
        ]);
    }
    
    public function testLogoutFailed(): void
    {
        $this->seed([UserSeeder::class]);
        $this->delete('api/users/logout', headers:[
                'Authorization' => 'salah'
            ]
        )->assertStatus(401)
        ->assertJson([
            'errors' => [
                'message' => [
                    'Unauthorized'
                ]
            ]
        ]);
    }
}
