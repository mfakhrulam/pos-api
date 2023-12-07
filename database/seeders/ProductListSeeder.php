<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email', 'test@mail.com')->first();
        $category = Category::query()->limit(2)->get();

        for($i = 0; $i < 3; $i++)  {
            Product::create([
                'name' => $i . ' test product kategori pertama ',
                'image' => 'test' . $i,
                'description' => 'test' . $i,
                'price' => '24000',
                'is_for_sale' => true,
                'category_id' => $category[0]->id,
                'user_id' => $user->id
            ]);
        }
        for($i = 0; $i < 2; $i++)  {
            Product::create([
                'name' => $i . ' test product pertama gaada kategori',
                'image' => 'test' . $i,
                'description' => 'test' . $i,
                'price' => '24000',
                'is_for_sale' => true,
                'category_id' => null,
                'user_id' => $user->id
            ]);
        }
        for($i = 0; $i < 4; $i++)  {
            Product::create([
                'name' => $i . ' test product kategori kedua',
                'image' => 'test' . $i,
                'description' => 'test' . $i,
                'price' => '24000',
                'is_for_sale' => true,
                'category_id' => $category[1]->id,
                'user_id' => $user->id
            ]);
        }
    }
}
