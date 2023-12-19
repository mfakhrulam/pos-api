<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\OutletController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\ApiAuthMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::post('/users', [UserController::class, 'register']);
Route::post('/users/send_otp', [UserController::class, 'sendOtp']);
Route::post('/users/verify_otp', [UserController::class, 'verifyOtp']);

Route::post('/users/login', [UserController::class, 'login']);  

Route::middleware(ApiAuthMiddleware::class)->group(function() {
// Route::middleware('auth:sanctum')->group(function() {
  Route::get('/users/current', [UserController::class, 'get']);
  Route::patch('/users/current', [UserController::class, 'update']);
  Route::delete('/users/logout', [UserController::class, 'logout']);

  Route::post('/outlets', [OutletController::class, 'create']);
  Route::get('/outlets', [OutletController::class, 'getAll']);
  Route::get('/outlets/{id}', [OutletController::class, 'get'])->where('id', '[0-9]+');
  Route::put('/outlets/{id}', [OutletController::class, 'update'])->where('id', '[0-9]+');
  Route::delete('/outlets/{id}', [OutletController::class, 'delete'])->where('id', '[0-9]+');
  
  Route::post('/employees', [EmployeeController::class, 'create']);
  Route::get('/employees', [EmployeeController::class, 'search']);
  Route::get('/employees/{id}', [EmployeeController::class, 'get'])->where('id', '[0-9]+');
  Route::put('/employees/{id}', [EmployeeController::class, 'update'])->where('id', '[0-9]+');
  Route::delete('/employees/{id}', [EmployeeController::class, 'delete'])->where('id', '[0-9]+');
  
  Route::post('/customers', [CustomerController::class, 'create']);
  Route::get('/customers', [CustomerController::class, 'search']);
  Route::get('/customers/{id}', [CustomerController::class, 'get'])->where('id', '[0-9]+');
  Route::put('/customers/{id}', [CustomerController::class, 'update'])->where('id', '[0-9]+');
  Route::delete('/customers/{id}', [CustomerController::class, 'delete'])->where('id', '[0-9]+');
  
  Route::post('/categories', [CategoryController::class, 'create']);
  Route::get('/categories', [CategoryController::class, 'search']);
  Route::get('/categories/{id}', [CategoryController::class, 'get'])->where('id', '[0-9]+');
  Route::put('/categories/{id}', [CategoryController::class, 'update'])->where('id', '[0-9]+');
  Route::delete('/categories/{id}', [CategoryController::class, 'delete'])->where('id', '[0-9]+');
  
  Route::post('/products', [ProductController::class, 'create']);
  Route::get('/products', [ProductController::class, 'search']);
  Route::get('/products/{id}', [ProductController::class, 'get'])->where('id', '[0-9]+');
  Route::put('/products/{id}', [ProductController::class, 'update'])->where('id', '[0-9]+');
  Route::delete('/products/{id}', [ProductController::class, 'delete'])->where('id', '[0-9]+');
  Route::delete('/products/{id}/delete_image', [ProductController::class, 'deleteImage'])->where('id', '[0-9]+');
});