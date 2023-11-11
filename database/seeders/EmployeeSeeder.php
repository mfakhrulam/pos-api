<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email', 'test@mail.com')->first();
        $outlet = Outlet::query()->limit(1)->first();

        Employee::create([
            'name' => 'test',
            'phone' => '08123456789',
            'pin' => '1234',
            'email' => '',
            'role' => '1',
            'user_id' => $user->id,
        ]);
    }
}
