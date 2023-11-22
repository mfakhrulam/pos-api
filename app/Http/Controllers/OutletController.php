<?php

namespace App\Http\Controllers;

use App\Http\Requests\OutletCreateRequest;
use App\Http\Requests\OutletUpdateRequest;
use App\Http\Resources\OutletResource;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OutletController extends Controller
{
    private function getOutlet(User $user, int $idOutlet): Outlet
    {
        $outlet = Outlet::where('id', $idOutlet)->where('user_id', $user->id)->first();
        if(!$outlet) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    'message' => [
                        'Outlet not found'
                    ]
                ]
            ])->setStatusCode(404));
        }

        return $outlet;
    }

    public function create(OutletCreateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = Auth::user();

        $outlet = new Outlet($data);
        $outlet->user_id = $user->id;
        $outlet->save();

        return (new OutletResource($outlet))->response()->setStatusCode(201);
    }

    public function get(int $id): OutletResource
    {
        $user = Auth::user();
        $outlet = $this->getOutlet($user, $id);

        return new OutletResource($outlet->loadMissing('employees'));
    }

    public function update(int $id, OutletUpdateRequest $request): OutletResource
    {
        $user = Auth::user();
        $outlet = $this->getOutlet($user, $id);

        $data = $request->validated();
        $outlet->fill($data);
        $outlet->save();

        return new OutletResource($outlet);
    }

    public function delete(int $id): JsonResponse
    {
        $user = Auth::user();
        $outlet = $this->getOutlet($user, $id);

        $outlet->delete();
        return response()->json([
            'data' => true
        ])->setStatusCode(200);
    }

    public function getAll(): JsonResponse
    {
        $user = Auth::user();
        $outlets = Outlet::where('user_id', $user->id)->get();
        return (OutletResource::collection($outlets->loadMissing('employees')))->response()->setStatusCode(200);
    }
}
