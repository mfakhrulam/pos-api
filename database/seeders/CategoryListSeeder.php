<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategoryListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email', 'test@mail.com')->first();
        for ($i = 0; $i < 5; $i++) {
            Category::create([
                'name' => 'test category ' . $i,
                'user_id' => $user->id
            ]);
        }
    }
}
