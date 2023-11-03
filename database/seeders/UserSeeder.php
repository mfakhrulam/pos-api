<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'test',
            'email' => 'test@mail.com',
            'phone' => '0888888888',
            'password' => Hash::make('12345678'),
            'token' => 'test'
        ]);

        User::create([
            'name' => 'fakhrul',
            'email' => 'fakhrul@mail.com',
            'phone' => '08123456789',
            'password' => Hash::make('12345678'),
            'token' => 'fakhrul'
        ]);
    }
}
