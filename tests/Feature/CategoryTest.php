<?php

namespace Tests\Feature;

use App\Models\Category;
use Database\Seeders\CategoryListSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testCreateSuccess(): void
    {
        $this->seed([UserSeeder::class]);
        $this->post('/api/categories', [
            'name' => 'test category'
        ], [
            'Authorization' => 'test'
        ])->assertStatus(201)
        ->assertJson([
            'data' => [
                'name' => 'test category'
            ]
        ]);
    }

    public function testCreateFailed(): void
    {
        $this->seed([UserSeeder::class]);

        $this->post('/api/categories', [
            'name' => ''
        ], [
            'Authorization' => 'test'
        ])->assertStatus(400)
        ->assertJson([
            'errors' => [
                'name' => [
                    'The name field is required.'
                ]
            ]
        ]);
    }

    public function testCreateUnauthorized(): void
    {
        $this->seed([UserSeeder::class]);

        $this->post('/api/categories', [
            'name' => 'test category'
        ], [
            'Authorization' => ''
        ])->assertStatus(401)
        ->assertJson([
            'errors' => [
                'message' => [
                    'Unauthorized'
                ]
            ]
        ]);
    }

    public function testGetSuccess(): void
    {
        $this->seed([UserSeeder::class, CategorySeeder::class]);
        $category = Category::query()->limit(1)->first();

        $this->get('api/categories/' . $category->id, [
            'Authorization' => 'test'
        ])->assertStatus(200)
        ->assertJson([
            'data' => [
                'name' => 'test category',
            ]
        ]);
    }
    
    public function testGetNotFound(): void
    {
        $this->seed([UserSeeder::class, CategorySeeder::class]);
        $category = Category::query()->limit(1)->first();

        $this->get('api/categories/' . ($category->id + 1), [
            'Authorization' => 'test'
        ])->assertStatus(404)
        ->assertJson([
            'errors' => [
                'message' => [
                    'Category not found'
                ]
            ]
        ]);
    }

    public function testGetOtherUserCategory(): void
    {
        $this->seed([UserSeeder::class, CategorySeeder::class]);
        $category = Category::query()->limit(1)->first();

        $this->get('api/categories/' . $category->id, [
            'Authorization' => 'test2'
        ])->assertStatus(404)
        ->assertJson([
            'errors' => [
                'message' => [
                    'Category not found'
                ]
            ]
        ]);
    }

    public function testGetUnauthorized(): void
    {
        $this->seed([UserSeeder::class, CategorySeeder::class]);
        $category = Category::query()->limit(1)->first();

        $this->get('api/categories/' . $category->id, [
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

    public function testUpdateSuccess(): void
    {
        $this->seed([UserSeeder::class, CategorySeeder::class]);
        $category = Category::query()->limit(1)->first();

        $this->put('api/categories/' . $category->id, [
            'name' => 'category'
        ], [
            'Authorization' => 'test'
        ])->assertStatus(200)
        ->assertJson([
            'data' => [
                'name' => 'category'
            ]
        ]);
    }

    public function testUpdateValidationError(): void
    {
        $this->seed([UserSeeder::class, CategorySeeder::class]);
        $category = Category::query()->limit(1)->first();

        $this->put('api/categories/' . $category->id, [
            'name' => ''
        ], [
            'Authorization' => 'test'
        ])->assertStatus(400)
        ->assertJson([
            'errors' => [
                'name' => [
                    'The name field is required.'
                ]
            ]
        ]);
    }

    public function testUpdateUnauthorized(): void
    {
        $this->seed([UserSeeder::class, CategorySeeder::class]);
        $category = Category::query()->limit(1)->first();

        $this->put('api/categories/' . $category->id, [
            'name' => 'category'
        ], [
            'Authorization' => 'tokensalah'
        ])->assertStatus(401)
        ->assertJson([
            'errors' => [
                'message' => [
                    'Unauthorized'
                ]
            ]
        ]);
    }

    public function testUpdateNotFound(): void
    {
        $this->seed([UserSeeder::class, CategorySeeder::class]);
        $category = Category::query()->limit(1)->first();

        $this->put('api/categories/' . ($category->id + 1), [
            'name' => 'category'
        ], [
            'Authorization' => 'test'
        ])->assertStatus(404)
        ->assertJson([
            'errors' => [
                'message' => [
                    'Category not found'
                ]
            ]
        ]);
    }

    public function testDeleteSuccess(): void
    {
        $this->seed([UserSeeder::class, CategorySeeder::class]);
        $category = Category::query()->limit(1)->first();

        $this->delete('api/categories/' . $category->id, [], [
            'Authorization' => 'test'
        ])->assertStatus(200)
        ->assertJson([
            'data' => true
        ]);
    }

    public function testDeleteNotFound(): void
    {
        $this->seed([UserSeeder::class, CategorySeeder::class]);
        $category = Category::query()->limit(1)->first();

        $this->delete('api/categories/' . ($category->id+1), [], [
            'Authorization' => 'test'
        ])->assertStatus(404)
        ->assertJson([
            'errors' => [
                'message' => [
                    'Category not found'
                ]
            ] 
        ]);
    }

    public function testSearchByNameSuccess(): void
    {
        $this->seed([UserSeeder::class, CategoryListSeeder::class]);
        $response = $this->get('/api/categories?name=test category', [
            'Authorization' => 'test'
        ])->assertStatus(200)->json();
        self::assertEquals(5, count($response['data']));

        Log::info(json_encode($response, JSON_PRETTY_PRINT));
    }

    public function testGetAllSuccess(): void
    {
        $this->seed([UserSeeder::class, CategoryListSeeder::class]);
        $response = $this->get('/api/categories', [
            'Authorization' => 'test'
        ])->assertStatus(200)->json();
        self::assertEquals(5, count($response['data']));

        // Log::info(encode_json($response, JSON_PRETTY_PRINT));
    }
    
    public function testGetAllEmpty(): void
    {
        $this->seed([UserSeeder::class, CategoryListSeeder::class]);
        $response = $this->get('/api/categories?name=tidak ada', [
            'Authorization' => 'test'
        ])->assertStatus(200)->json();
        self::assertEquals(0, count($response['data']));
        self::assertEmpty($response['data']);
    }

    public function testGetAllUnauthorized(): void
    {
        $this->seed([UserSeeder::class, CategoryListSeeder::class]);
        $this->get('/api/categories', [
            'Authorization' => ''
        ])->assertStatus(401)
        ->assertJson([
            'errors' => [
                'message' => [
                    'Unauthorized'
                ]
            ]
        ]);
    }
}
