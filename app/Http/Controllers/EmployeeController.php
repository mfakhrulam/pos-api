<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeCreateRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeController extends Controller
{
    public function create(EmployeeCreateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = Auth::user();

        $employee = new Employee();

        $employee->name = $data['name'];
        $employee->phone = $data['phone'];
        $employee->pin = $data['pin'];
        $employee->email = $data['email'];
        $employee->role = $data['role'];
        $employee->user_id = $user->id;

        $employee->save();
        $employee->outlets()->attach($data['outletIds']);

        return (new EmployeeResource($employee))->response()->setStatusCode(201);
    }
}
