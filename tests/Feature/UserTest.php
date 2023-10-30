<?php

namespace Tests\Feature;

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
}
