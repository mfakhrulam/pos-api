<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{   
    private function getCategory(User $user, int $idCategory): Category
    {
        $category = Category::where('id', $idCategory)->where('user_id', $user->id)->first();
        if(!$category) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    'message' => [
                        'Category not found'
                    ]
                ]
            ])->setStatusCode(404));
        }

        return $category;
    }


    public function create(CategoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = Auth::user();

        $category = new Category($data);
        $category->user_id = $user->id;
        $category->save();

        return (new CategoryResource($category))->response()->setStatusCode(201);
    }

    public function get(int $id): CategoryResource
    {
        $user = Auth::user();
        $category = $this->getCategory($user, $id);

        return new CategoryResource($category);
    }

    public function update(int $id, CategoryRequest $request): CategoryResource
    {
        $user = Auth::user();
        $category = $this->getCategory($user, $id);

        $data = $request->validated();
        $category->fill($data);
        $category->save();

        return new CategoryResource($category);
    }

    public function delete(int $id): JsonResponse
    {
        $user = Auth::user();
        $category = $this->getCategory($user, $id);

        $category->delete();
        return response()->json([
            'data' => true
        ])->setStatusCode(200);
    }

    public function search(Request $request): JsonResponse
    {
        $user = Auth::user();
        $categories = Category::query()->where('user_id', $user->id);

        $categories = $categories->where(function (Builder $builder) use ($request){
            $name = $request->input('name');
            if($name) {
                $builder->orWhere('name', 'like', '%' . $name . '%');
            }
        });

        $categories = $categories->get();
        return (CategoryResource::collection($categories))->response()->setStatusCode(200);
    }
}
