<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeeListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email', 'test@mail.com')->first();
        for($i = 0; $i < 5; $i++)  {
            Employee::create([
                'name' => 'employee ' . $i,
                'phone' => '0812345678' . $i,
                'pin' => '123' . $i,
                'email' => 'employee' . $i . '@mail.com',
                'role' => '1',
                'user_id' => $user->id,
            ]);
        }
    }
}
