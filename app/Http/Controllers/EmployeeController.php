<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeController extends Controller
{
    private function getEmployee(User $user, int $idEmployee): Employee
    {
        $employee = Employee::where('id', $idEmployee)->where('user_id', $user->id)->first();
        if(!$employee) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    'message' => [
                        'Employee not found'
                    ]
                ]
            ])->setStatusCode(404));
        }

        return $employee;
    }

    public function create(EmployeeRequest $request): JsonResponse
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

        return (new EmployeeResource($employee->loadMissing('outlets')))->response()->setStatusCode(201);
    }

    public function get(int $id): EmployeeResource
    {
        $user = Auth::user();
        $employee = $this->getEmployee($user, $id);

        return new EmployeeResource($employee->loadMissing('outlets'));
    }

    
}
