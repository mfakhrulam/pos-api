<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Database\Seeder;

class OutletListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();
        for ($i = 0; $i < 5; $i++) {
            Outlet::create([
                'name' => 'test restoran '. $i,
                'address' => 'yogyakarta '. $i,
                'phone' => '08123456789'. $i,
                'email' => 'restoran'. $i .'@gmail.com',
                'is_active' => true,
                'user_id' => $user->id
            ]);
        }
    }
}
