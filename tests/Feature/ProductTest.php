<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Database\Seeders\CategoryListSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ProductListSeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testCreateSuccessWithImage(): void
    {
        $this->seed([UserSeeder::class, CategorySeeder::class]);
        $category = Category::query()->limit(1)->first();
        Storage::fake('public/images');

        $this->post('/api/products', [
            'name' => 'test product',
            'image' => UploadedFile::fake()->image('product.jpg'),
            'description' => '',
            'price' => '30000',
            'is_for_sale' => true,
            'category_id' => $category->id
        ], [
            'Authorization' => 'test'
        ])->assertStatus(201)
        ->assertJson([
            'data' => [
                'name' => 'test product',
                'description' => '',
                'price' => '30000',
                'is_for_sale' => true,
                'category' => array()
            ]
        ]);

        $product = Product::query()->limit(1)->first();
        $this->assertNotNull($product->image);
        // Storage::disk('public')->assertExists($product->image); 
    }

    public function testCreateSuccessWithoutImage(): void
    {
        $this->seed([UserSeeder::class, CategorySeeder::class]);
        $category = Category::query()->limit(1)->first();

        $this->post('/api/products', [
            'name' => 'test product',
            'description' => '',
            'price' => '30000',
            'is_for_sale' => true,
            'category_id' => $category->id
        ], [
            'Authorization' => 'test'
        ])->assertStatus(201)
        ->assertJson([
            'data' => [
                'name' => 'test product',
                'description' => '',
                'price' => '30000',
                'is_for_sale' => true,
                'category' => array()
            ]
        ]);

        $product = Product::query()->limit(1)->first();
        $this->assertEquals('images/default.jpg', $product->image);
        // Storage::disk('public')->assertExists($product->image); 
    }

    public function testCreateFailed(): void
    {
        $this->seed([UserSeeder::class, CategorySeeder::class]);
        $category = Category::first();

        $this->post('/api/products', [
            'name' => 'test product',
            'image' => '',
            'description' => '',
            'price' => '',
            'is_for_sale' => true,
            'category_id' => $category->id
        ], [
            'Authorization' => 'test'
        ])->assertStatus(400)
        ->assertJson([
            'errors' => [
                'price' => [
                    'The price field is required.'
                ],
            ]
        ]);
    }

    public function testCreateUnauthorized(): void
    {
        $this->seed([UserSeeder::class, CategorySeeder::class]);
        $category = Category::query()->limit(1)->first();

        $this->post('/api/products', [
            'name' => 'test product',
            'image' => '',
            'description' => '',
            'price' => '30000',
            'is_for_sale' => true,
            'category_id' => $category->id
        ], [
            'Authorization' => 'salah token'
        ])->assertStatus(401)
        ->assertJson([
            'errors' => [
                'message' => [
                    'Unauthorized'
                ],
            ]
        ]);
    }

    public function testGetSuccess(): void
    {
        $this->seed([UserSeeder::class, CategorySeeder::class, ProductSeeder::class]);
        $product = Product::query()->limit(1)->first();

        $this->get('api/products/' . $product->id, [
            'Authorization' => 'test'
        ])->assertStatus(200)
        ->assertJson([
            'data' => [
                'name' => 'test product',
                'image' => 'test',
                'description' => 'test',
                'price' => '24000',
                'is_for_sale' => true,
            ]
        ]);
    }

    public function testGetNotFound(): void
    {
        $this->seed([UserSeeder::class, CategorySeeder::class, ProductSeeder::class]);
        $product = Product::query()->limit(1)->first();

        $this->get('api/products/' . ($product->id+1), [
            'Authorization' => 'test'
        ])->assertStatus(404)
        ->assertJson([
            'errors' => [
                'message' => [
                    'Product not found'
                ]
            ]
        ]);
    }

    public function testGetOutherUserProduct(): void
    {
        $this->seed([UserSeeder::class, CategorySeeder::class, ProductSeeder::class]);
        $product = Product::query()->limit(1)->first();

        $this->get('api/products/' . $product->id, [
            'Authorization' => 'test2'
        ])->assertStatus(404)
        ->assertJson([
            'errors' => [
                'message' => [
                    'Product not found'
                ]
            ]
        ]);
    }

    public function testUpdateSuccessWithImage(): void
    {
        $this->seed([UserSeeder::class, CategorySeeder::class, ProductSeeder::class]);
        $product = Product::query()->limit(1)->first();
        Storage::fake('public/images');

        $this->put('/api/products/' . $product->id, [
            'name' => 'test product diganti',
            'image' => UploadedFile::fake()->image('product1.jpg'),
            'description' => 'deskripsi produk diperbaharui',
            'price' => '50000',
            'is_for_sale' => true,
            'category_id' => ''
        ], [
            'Authorization' => 'test'
        ])->assertStatus(200)
        ->assertJson([
            'data' => [
                'name' => 'test product diganti',
                'description' => 'deskripsi produk diperbaharui',
                'price' => '50000',
                'is_for_sale' => true,
                'category' => ''
            ]
        ]);

        $product = Product::query()->limit(1)->first();
        $this->assertNotNull($product->image);
    }

    public function testUpdateSuccessWithoutImage(): void
    {
        $this->seed([UserSeeder::class, CategorySeeder::class, ProductSeeder::class]);
        $product = Product::query()->limit(1)->first();
        $category = Category::query()->limit(1)->first();
        Storage::fake('public/images');

        $this->put('/api/products/' . $product->id, [
            'name' => 'test product diganti',
            'image' => '',
            'description' => 'deskripsi produk diperbaharui',
            'price' => '50000',
            'is_for_sale' => true,
            'category_id' => $category->id
        ], [
            'Authorization' => 'test'
        ])->assertStatus(200)
        ->assertJson([
            'data' => [
                'name' => 'test product diganti',
                'description' => 'deskripsi produk diperbaharui',
                'price' => '50000',
                'is_for_sale' => true,
                'category' => array()
            ]
        ])->json();

        $product = Product::query()->limit(1)->first();
        $this->assertNotNull($product->image);
    }

    public function testUpdateFailedValidation(): void
    {
        $this->seed([UserSeeder::class, CategorySeeder::class, ProductSeeder::class]);
        $product = Product::query()->limit(1)->first();

        $this->put('/api/products/' . $product->id, [
            'name' => '',
            'image' => '',
            'description' => 'deskripsi produk diperbaharui',
            'price' => '',
            'is_for_sale' => true,
            'category_id' => ''
        ], [
            'Authorization' => 'test'
        ])->assertStatus(400)
        ->assertJson([
            'errors' => [
                'name' => [
                    'The name field is required.'
                ],
                'price' => [
                    'The price field is required.'
                ],
            ]
        ]);
    }

    public function testUpdateFailedUnauthorized(): void
    {
        $this->seed([UserSeeder::class, CategorySeeder::class, ProductSeeder::class]);
        $product = Product::query()->limit(1)->first();

        $this->put('/api/products/' . $product->id, [
            'name' => 'test product diganti',
            'image' => '',
            'description' => 'deskripsi produk diperbaharui',
            'price' => '50000',
            'is_for_sale' => true,
            'category_id' => ''
        ], [
            'Authorization' => 'salah token'
        ])->assertStatus(401)
        ->assertJson([
            'errors' => [
                'message' => [
                    'Unauthorized'
                ]
            ]
        ]);
    }

    public function testUpdateFailedNotFound(): void
    {
        $this->seed([UserSeeder::class, CategorySeeder::class, ProductSeeder::class]);
        $product = Product::query()->limit(1)->first();

        $this->put('/api/products/' . ($product->id+1), [
            'name' => 'test product diganti',
            'image' => '',
            'description' => 'deskripsi produk diperbaharui',
            'price' => '50000',
            'is_for_sale' => true,
            'category_id' => ''
        ], [
            'Authorization' => 'test'
        ])->assertStatus(404)
        ->assertJson([
            'errors' => [
                'message' => [
                    'Product not found'
                ]
            ]
        ]);
    }

    public function testDeleteImageSuccess(): void
    {
        $this->seed([UserSeeder::class, CategorySeeder::class, ProductSeeder::class]);
        $product = Product::query()->limit(1)->first();

        $this->delete('api/products/'. $product->id . '/delete_image', [], [
            'Authorization' => 'test'
        ])->assertStatus(200)
        ->assertJson([
            'data' => true
        ]);
    }

    public function testDeleteImageNotFound(): void
    {
        $this->seed([UserSeeder::class, CategorySeeder::class, ProductSeeder::class]);
        $product = Product::query()->limit(1)->first();

        $this->delete('api/products/'. ($product->id+1) . '/delete_image', [], [
            'Authorization' => 'test'
        ])->assertStatus(404)
        ->assertJson([
            'errors' => [
                'message' => [
                    'Product not found'
                ]
            ]
        ]);
    }

    public function testDeleteSuccess(): void
    {
        $this->seed([UserSeeder::class, CategorySeeder::class, ProductSeeder::class]);
        $product = Product::query()->limit(1)->first();

        $this->delete('api/products/'. $product->id , [], [
            'Authorization' => 'test'
        ])->assertStatus(200)
        ->assertJson([
            'data' => true
        ]);
    }

    public function testDeleteNotFound(): void
    {
        $this->seed([UserSeeder::class, CategorySeeder::class, ProductSeeder::class]);
        $product = Product::query()->limit(1)->first();

        $this->delete('api/products/'. ($product->id+1) , [], [
            'Authorization' => 'test'
        ])->assertStatus(404)
        ->assertJson([
            'errors' => [
                'message' => [
                    'Product not found'
                ]
            ] 
        ]);
    }

    public function testSearchWithoutFilterSuccess(): void
    {
        $this->seed([UserSeeder::class, CategoryListSeeder::class, ProductListSeeder::class]);
        $response = $this->get('api/products', [
            'Authorization' => 'test'
        ])->assertStatus(200)->json();

        self::assertEquals(9, count($response['data']));
    }

    public function testSearchByNameSuccess(): void
    {
        $this->seed([UserSeeder::class, CategoryListSeeder::class, ProductListSeeder::class]);
        $response1 = $this->get('api/products?name=pertama', [
            'Authorization' => 'test'
        ])->assertStatus(200)->json();
        $response2 = $this->get('api/products?name=kedua', [
            'Authorization' => 'test'
        ])->assertStatus(200)->json();

        self::assertEquals(5, count($response1['data']));
        self::assertEquals(4, count($response2['data']));
    }

    public function testFilterByCategorySuccess(): void
    {
        $this->seed([UserSeeder::class, CategoryListSeeder::class, ProductListSeeder::class]);
        $category = Category::query()->limit(2)->get();
        
        $response1 = $this->get('api/products?category_id=' . $category[0]->id, [
            'Authorization' => 'test'
        ])->assertStatus(200)->json();
        $response2 = $this->get('api/products?category_id=' . $category[1]->id, [
            'Authorization' => 'test'
        ])->assertStatus(200)->json();
        $response3 = $this->get('api/products?category_id=', [
            'Authorization' => 'test'
        ])->assertStatus(200)->json();

        self::assertEquals(3, count($response1['data']));
        self::assertEquals(4, count($response2['data']));
        self::assertEquals(2, count($response3['data']));
    }

    public function testSearchByNameAndCategorySuccess(): void
    {
        $this->seed([UserSeeder::class, CategoryListSeeder::class, ProductListSeeder::class]);
        $category = Category::query()->limit(2)->get();
        
        $response1 = $this->get('api/products?name=pertama&category_id=' . $category[0]->id, [
            'Authorization' => 'test'
        ])->assertStatus(200)->json();
        $response2 = $this->get('api/products?name=pertama&category_id=' . $category[1]->id , [
            'Authorization' => 'test'
        ])->assertStatus(200)->json();
        $response3 = $this->get('api/products?name=pertama&category_id=', [
            'Authorization' => 'test'
        ])->assertStatus(200)->json();

        self::assertEquals(3, count($response1['data']));
        self::assertEquals(0, count($response2['data']));
        self::assertEquals(2, count($response3['data']));
    }

    public function testSearchSortByNewestSuccess(): void
    {
        $this->seed([UserSeeder::class, CategoryListSeeder::class, ProductListSeeder::class]);
        $this->get('api/products?sort_by=newest', [
            'Authorization' => 'test'
        ])->assertStatus(200)->json();
    }

    public function testSearchSortByOldestSuccess(): void
    {
        $this->seed([UserSeeder::class, CategoryListSeeder::class, ProductListSeeder::class]);
        $this->get('api/products?sort_by=oldest', [
            'Authorization' => 'test'
        ])->assertStatus(200)->json();
    }

    public function testSearchSortByAZSuccess(): void
    {
        $this->seed([UserSeeder::class, CategoryListSeeder::class, ProductListSeeder::class]);
        $this->get('api/products?name=gaada&sort_by=az', [
            'Authorization' => 'test'
        ])->assertStatus(200)
        ->assertJson([
            'data' => [
                [
                    "name" => "0 test product pertama gaada kategori",
                    "image" => "test0",
                    "description" => "test0",
                    "price" => "24000.00",
                    "is_for_sale" => 1,
                    "category" => null
                ],
                [
                    "name" => "1 test product pertama gaada kategori",
                    "image" => "test1",
                    "description" => "test1",
                    "price" => "24000.00",
                    "is_for_sale" => 1,
                    "category" => null
                ]
            ]
        ]);
    }

    public function testSearchSortByZASuccess(): void
    {
        $this->seed([UserSeeder::class, CategoryListSeeder::class, ProductListSeeder::class]);
        $this->get('api/products?name=gaada&sort_by=za', [
            'Authorization' => 'test'
        ])->assertStatus(200)
        ->assertJson([
            'data' => [
                [
                    "name" => "1 test product pertama gaada kategori",
                    "image" => "test1",
                    "description" => "test1",
                    "price" => "24000.00",
                    "is_for_sale" => 1,
                    "category" => null
                ],
                [
                    "name" => "0 test product pertama gaada kategori",
                    "image" => "test0",
                    "description" => "test0",
                    "price" => "24000.00",
                    "is_for_sale" => 1,
                    "category" => null
                ]
            ]
        ]);
    }
}
