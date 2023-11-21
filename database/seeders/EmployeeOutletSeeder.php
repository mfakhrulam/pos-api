<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeeOutletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = Employee::all();
        $outlets = Outlet::all();

        foreach ($employees as $employee) {
            // Attaching random outlets to each employee
            $randomOutlets = $outlets->random(rand(1, 3)); // Change the range as needed
            $employee->outlets()->attach($randomOutlets);
        }
    }
}
