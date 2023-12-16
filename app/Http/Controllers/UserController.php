<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Notifications\OtpNotification;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function register(UserRegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        if(User::where('email', $data['email'])->orWhere('phone', $data['phone'])->count() == 1)
        {
            // ada di database?
            throw new HttpResponseException(response([
                'errors' => [
                    'email' => [
                        'email already registered'
                    ],
                    'phone' => [
                        'phone already registered'
                    ]
                ]
            ], 400));
        }

        $user = new User($data);
        // $token = $user->createToken('auth_token')->plainTextToken;
        $user->password = Hash::make($data['password']);
        $user->save();

        return (new UserResource($user))->response()->setStatusCode(201);
    }

    public function sendOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(),[
            'email' => 'required|string|email|max:100'
        ]);

        if($validator->fails()){
            throw new HttpResponseException(response([
                'errors' => $validator->getMessageBag()
            ], 400));
        }

        $user = User::where('email', $request->email)->first();
        if(!$user) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    'message' => [
                        'User not found'
                    ]
                ]
            ])->setStatusCode(404));
        }

        if($user->hasVerifiedEmail()) {
            throw new HttpResponseException(response([
                'errors' => [
                    'message' => [
                        'The user has been verified, no OTP code required'
                    ]
                ]
            ], 400));
        }

        $otp = rand(1000, 9999);

        $user->update([
            'token' => $otp,
            'token_expired_at' => now()->addMinutes(5)
        ]);

        // can't send email with free account
        // $user->notify(new OtpNotification($user));
        // \Mail::to($request->email)->send(new sendmail($mail_details));
        
        return response()->json([
            'data' => [
                'otp' => $otp,
                'token_expired_at' => now()->addMinutes(5)
            ],
        ])->setStatusCode(200);
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'email' => 'required|string|email|max:100',
            'otp' => 'required|max:4'
        ]);

        if($validator->fails()){
            throw new HttpResponseException(response([
                'errors' => $validator->getMessageBag()
            ], 400));
        }

        // $user  = User::where([['email','=',$request->email],['otp','=',$request->otp]])->first();
        $user = User::where('email', $request->email)->first();

        if($user->hasVerifiedEmail()) {
            throw new HttpResponseException(response([
                'errors' => [
                    'message' => [
                        'The user has been verified, no OTP code required'
                    ]
                ]
            ], 400));
        }

        if(!$user->where('token', $request->otp) || $user->token_expired_at < now()) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    'message' => [
                        'OTP wrong or expired'
                    ]
                ]
            ])->setStatusCode(401));
        }

        $user->update([
            'token' => null,
            'token_expired_at' => null
        ]);

        $user->markEmailAsVerified();

        $accessToken = $user->createToken('auth_token')->plainTextToken;

        return (new UserResource($user))->additional([
            'access_token' => $accessToken,
            'token_type'   => 'Bearer'
        ])->response()->setStatusCode(200);
    }

    public function login(UserLoginRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = User::where('email', $data['email_or_phone'])->orWhere('phone', $data['email_or_phone'])->first();
       
        if(!$user || !Hash::check($data['password'], $user->password)) {
            throw new HttpResponseException(response([
                'errors' => [
                    'message' => [
                        'phone or email or password wrong'
                    ]
                ]
            ], 401));
        }

        if(!$user->hasVerifiedEmail()) {
            throw new HttpResponseException(response([
                'errors' => [
                    'message' => [
                        'Email has not been verified'
                    ]
                ]
            ], 401));
        }
        
        $accessToken = $user->createToken('auth_token')->plainTextToken;
        
        return (new UserResource($user))->additional([
            'access_token' => $accessToken,
            'token_type'   => 'Bearer'
        ])->response()->setStatusCode(200);
    }

    public function get(Request $request): UserResource
    {
        $user = $request->user();
        return new UserResource($user);
    }

    public function update(UserUpdateRequest $request): UserResource
    {
        $data = $request->validated();
        $user = $request->user();

        if(isset($data['name'])) {
            $user->name = $data['name'];
        }

        if(isset($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        if(isset($data['phone'])) {
            
            if(User::where('phone', $data['phone'])->count() >= 1 && $user->phone != $data['phone']) 
            {
                throw new HttpResponseException(response([
                    'errors' => [
                        'phone' => [
                            'phone already registered'
                        ]
                    ]
                ], 400));
            }

            $user->phone = $data['phone'];

        }

        if(isset($data['email'])) {
            
            if(User::where('email', $data['email'])->count() >= 1 && $user->email != $data['email']) 
            {
                throw new HttpResponseException(response([
                    'errors' => [
                        'email' => [
                            'email already registered'
                        ]
                    ]
                ], 400));
            }

            $user->email = $data['email'];

        }

        $user->save();
        return new UserResource($user);
    }

    public function logout(Request $request) : JsonResponse
    {
        $user = $request->user();
        $bearer = $request->bearerToken();
        $parts = explode('|', $bearer);
        $tokenId = $parts[0];
        $user->tokens()->where('id', $tokenId)->delete();
        
        return response()->json([
            'data' => true
        ])->setStatusCode(200);

        // $user = Auth::user();
        // $user->token = null;
        // $user->save();

        // return response()->json([
        //     'data' => true
        // ])->setStatusCode(200);
    }
}
