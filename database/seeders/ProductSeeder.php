<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email', 'test@mail.com')->first();
        $category = Category::query()->limit(1)->first();
        Product::create([
            'name' => 'test product',
            'image' => 'test',
            'description' => 'test',
            'price' => '24000',
            'is_for_sale' => true,
            'category_id' => $category->id,
            'user_id' => $user->id
        ]);
    }
}
