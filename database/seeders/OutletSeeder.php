<?php

namespace Database\Seeders;

use App\Models\Outlet;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OutletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email', 'test@mail.com')->first();
        Outlet::create([
            'name' => 'test restoran',
            'address' => 'yogyakarta',
            'phone' => '08123456789',
            'email' => 'restoran@gmail.com',
            'is_active' => true,
            'user_id' => $user->id
        ]);

        
    }
}
