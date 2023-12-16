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
            'email' => 'test1@mail.com',
            'phone' => '0888888888',
            'password' => Hash::make('12345678'),
            'token' => 'test'
        ]);

        User::create([
            'name' => 'test2',
            'email' => 'test2@mail.com',
            'phone' => '081234567891',
            'password' => Hash::make('12345678'),
            'token' => 'test2'
        ]);
    }
}
