<?php

namespace App\Http\Controllers;

use App\Http\Requests\OutletCreateRequest;
use App\Http\Resources\OutletResource;
use App\Models\Outlet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OutletController extends Controller
{
    public function create(OutletCreateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = Auth::user();

        $outlet = new Outlet($data);
        $outlet->user_id = $user->id;
        $outlet->save();

        return (new OutletResource($outlet))->response()->setStatusCode(201);
    }
}
