<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email', 'test@mail.com')->first();
        for ($i = 0; $i < 5; $i++) {
            Customer::create([
                'name' => 'test cust ' . $i,
                'phone' => '08123456789' . $i,
                'email' => 'cust'. $i .'@gmail.com',
                'gender' => '1',
                'user_id' => $user->id
            ]);
        }
    }
}
