<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    private $genderEnum = array(
        1 => "Laki-laki",
        2 => "Perempuan",
    );

    private function getCustomer(User $user, int $idCustomer): Customer
    {
        $customer = Customer::where('id', $idCustomer)->where('user_id', $user->id)->first();
        if(!$customer) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    'message' => [
                        'Customer not found'
                    ]
                ]
            ])->setStatusCode(404));
        }

        return $customer;
    }

    public function create(CustomerRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = Auth::user();

        $customer = new Customer($data);
        $customer->gender = is_numeric($data['gender']) ? $this->genderEnum[$data['gender']] : $data['gender'];
        $customer->user_id = $user->id;
        $customer->save();

        return (new CustomerResource($customer))->response()->setStatusCode(201);
    }

    public function get(int $id): CustomerResource
    {
        $user = Auth::user();
        
        $customer = $this->getCustomer($user, $id);
        return new CustomerResource($customer);
    }

    public function update(CustomerRequest $request, int $id): CustomerResource
    {
        $user = Auth::user();
        $customer = $this->getCustomer($user, $id);
        
        $data = $request->validated();
        $customer->fill($data);
        $customer->save();

        return new CustomerResource($customer);
    }

    public function delete(int $id): JsonResponse
    {
        $user = Auth::user();
        $customer = $this->getCustomer($user, $id);
        $customer->delete();
        return response()->json([
            'data' => true
        ])->setStatusCode(200);
    }

    public function search(Request $request): JsonResponse
    {
        $user = Auth::user();
        $customers = Customer::query()->where('user_id', $user->id);

        $customers = $customers->where(function (Builder $builder) use ($request){
            $name = $request->input('name');
            if($name) {
                $builder->orWhere('name', 'like', '%' . $name . '%');
            }
        });

        $customers = $customers->get();
        return (CustomerResource::collection($customers))->response()->setStatusCode(200);
    }
}
