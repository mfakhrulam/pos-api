<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class ProductController extends Controller
{
    private function getProduct(User $user, int $idProduct): Product
    {
        $product = Product::where('id', $idProduct)->where('user_id', $user->id)->first();
        if(!$product) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    'message' => [
                        'Product not found'
                    ]
                ]
            ])->setStatusCode(404));
        }

        return $product;
    }


    public function create(ProductRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = Auth::user();
        $product = new Product();

        // $check_image = $request->get('image') ?? 'None';
        
        // if($check_image != 'None') {

        if($request->get('image')) {
            $imageName = $user->id . time() . '.' . $request->image->extension();
            $request->file('image')->storeAs('public/images', $imageName);
            $product->image = 'images/' . $imageName;
        } else {
            $product->image = 'images/default.jpg';
        }

        $product->name = $data['name'];
        $product->description = $data['description'];
        $product->price = $data['price'];
        $product->is_for_sale = $data['is_for_sale'];
        $product->category_id = $data['category_id'];
        $product->user_id = $user->id;

        $product->save();

        return (new ProductResource($product))->response()->setStatusCode(201);
    }

    public function get(int $id): ProductResource
    {
        $user = Auth::user();
        $product = $this->getProduct($user, $id);

        return new ProductResource($product);
    }

    public function update(int $id, ProductRequest $request): ProductResource
    {
        $user = Auth::user();
        $product = $this->getProduct($user, $id);

        $data = $request->validated();
        $product->fill(collect($data)->except('image')->toArray());
        
        if($request->file('image')) {
            $imagePath = $product->image;
            if(File::exists($imagePath)) {
                File::delete($imagePath);
            }
            $imageName = $user->id . time() . '.' . $request->image->extension();
            $request->file('image')->storeAs('public/images', $imageName);
            $product->image = 'images/' . $imageName;
        }
        
        $product->category_id = $data['category_id'];
        $product->save();
        return new ProductResource($product);
    }

    public function deleteImage(int $id): JsonResponse
    {
        $user = Auth::user();
        $product = $this->getProduct($user, $id);
        $imagePath = $product->image;
        if(File::exists($imagePath)) {
            File::delete($imagePath);
        }
        $product->image = 'images/default.jpg';
        $product->save();
        return response()->json([
            'data' => true
        ])->setStatusCode(200);
    }

    public function delete(int $id): JsonResponse
    {
        $user = Auth::user();
        $product = $this->getProduct($user, $id);

        $product->delete();
        return response()->json([
            'data' => true
        ])->setStatusCode(200);
    }

    public function search(Request $request): JsonResponse
    {
        $user = Auth::user();
        $products = Product::query()->where('user_id', $user->id);

        $products = $products->where(function (Builder $builder) use ($request){

            if($request->filled('name')) {
                $builder->where('name', 'like', '%' . $request->input('name') . '%');
            }
            
            if($request->has('category_id')) {
                if($request->filled('category_id')) {
                    $builder->where('category_id', '=', $request->input('category_id'));
                } else {
                    $builder->whereNull('category_id');
                }
            }            
        });

        // sort by : newest, oldest, az, za 
        $sort_by = $request->input('sort_by') ?? 'az';

        switch (strtolower($sort_by)) {
            case 'az':
                $products->orderBy('name', 'asc');
                break;
                
            case 'za':
                $products->orderBy('name', 'desc');
                break;
            
            case 'newest':
                $products->orderBy('updated_at', 'desc');
                break;
            
            case 'oldest':
                $products->orderBy('updated_at', 'asc');
                break;

            default:
                $products->orderBy('name', 'asc');
        };

        $products = $products->get();
        return (ProductResource::collection($products))->response()->setStatusCode(200);
    }
}
