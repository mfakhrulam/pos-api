<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email', 'test@mail.com')->first();
        Customer::create([
            'name' => 'test cust',
            'phone' => '08123456789',
            'email' => '',
            'gender' => '1',
            'user_id' => $user->id
        ]);
    }
}
