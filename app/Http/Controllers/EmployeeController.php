<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeRequest;
use App\Http\Resources\EmployeeCollectionResource;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{
    private $roleEnum = array(
        1 => "Kasir",
        2 => "Manajer",
        3 => "Pemilik",
        4 => "Superadmin",
    );

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
        $employee->role = is_numeric($data['role']) ? $this->roleEnum[$data['role']] : $data['role'];
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

    public function update(EmployeeRequest $request, int $id): EmployeeResource
    {
        $data = $request->validated();
        $user = Auth::user();
        $employee = $this->getEmployee($user, $id);
        $employee->fill(collect($data)->except('outletIds')->toArray());
        $employee->role = is_numeric($data['role']) ? $this->roleEnum[$data['role']] : $data['role'];
        $employee->save();
        $employee->outlets()->sync($data['outletIds']);

        return new EmployeeResource($employee->loadMissing('outlets'));
    }

    public function delete(int $id): JsonResponse
    {
        $user = Auth::user();
        $employee = $this->getEmployee($user, $id);

        $employee->outlets()->detach();
        $employee->delete();
        return response()->json([
            'data' => true
        ])->setStatusCode(200);
    }

    public function search(Request $request): JsonResponse
    {
        $user = Auth::user();
        $employees = Employee::query()->where('user_id', $user->id);

        $employees = $employees->where(function (Builder $builder) use ($request){
            $name = $request->input('name');
            if($name) {
                $builder->orWhere('name', 'like', '%' . $name . '%');
            }

            $outletId = $request->input('outletid');
            if($outletId) {
                $builder->whereHas('outlets', function (Builder $builder) use ($outletId){
                    $builder->where('outlet_id', '=', $outletId);
                });
            }
        });


        $employees = $employees->get();
        return (EmployeeResource::collection($employees))->response()->setStatusCode(200);

    }
}
