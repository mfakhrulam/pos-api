<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\OutletController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\ApiAuthMiddleware;
use App\Http\Middleware\EmployeeAuthMiddleware;
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
  
  Route::post('/employees/login', [EmployeeController::class, 'login']);
  Route::delete('/employees/logout', [EmployeeController::class, 'logout']);
  Route::post('/employees/register_owner', [EmployeeController::class, 'registerOwner']);
  
  Route::post('/outlets', [OutletController::class, 'create'])->middleware('role:Pemilik');
  Route::get('/outlets', [OutletController::class, 'getAll']);
  Route::get('/outlets/{id}', [OutletController::class, 'get'])->where('id', '[0-9]+');
  Route::put('/outlets/{id}', [OutletController::class, 'update'])->where('id', '[0-9]+')->middleware('role:Pemilik');
  Route::delete('/outlets/{id}', [OutletController::class, 'delete'])->where('id', '[0-9]+')->middleware('role:Pemilik');
  
  Route::post('/employees', [EmployeeController::class, 'create'])->middleware('role:Pemilik');
  Route::get('/employees', [EmployeeController::class, 'search']);
  Route::get('/employees/{id}', [EmployeeController::class, 'get'])->where('id', '[0-9]+');
  Route::put('/employees/{id}', [EmployeeController::class, 'update'])->where('id', '[0-9]+')->middleware('role:Pemilik,Manajer');
  Route::delete('/employees/{id}', [EmployeeController::class, 'delete'])->where('id', '[0-9]+')->middleware('role:Pemilik');
  
  Route::post('/customers', [CustomerController::class, 'create'])->middleware('role:Pemilik');
  Route::get('/customers', [CustomerController::class, 'search']);
  Route::get('/customers/{id}', [CustomerController::class, 'get'])->where('id', '[0-9]+');
  Route::put('/customers/{id}', [CustomerController::class, 'update'])->where('id', '[0-9]+')->middleware('role:Pemilik');
  Route::delete('/customers/{id}', [CustomerController::class, 'delete'])->where('id', '[0-9]+')->middleware('role:Pemilik');
  
  Route::post('/categories', [CategoryController::class, 'create'])->middleware('role:Pemilik,Manajer');
  Route::get('/categories', [CategoryController::class, 'search']);
  Route::get('/categories/{id}', [CategoryController::class, 'get'])->where('id', '[0-9]+');
  Route::put('/categories/{id}', [CategoryController::class, 'update'])->where('id', '[0-9]+')->middleware('role:Pemilik,Manajer');
  Route::delete('/categories/{id}', [CategoryController::class, 'delete'])->where('id', '[0-9]+')->middleware('role:Pemilik');
  
  Route::post('/products', [ProductController::class, 'create'])->middleware('role:Pemilik,Manajer');
  Route::get('/products', [ProductController::class, 'search']);
  Route::get('/products/{id}', [ProductController::class, 'get'])->where('id', '[0-9]+');
  Route::put('/products/{id}', [ProductController::class, 'update'])->where('id', '[0-9]+')->middleware('role:Pemilik,Manajer');
  Route::delete('/products/{id}', [ProductController::class, 'delete'])->where('id', '[0-9]+')->middleware('role:Pemilik');
  Route::delete('/products/{id}/delete_image', [ProductController::class, 'deleteImage'])->where('id', '[0-9]+')->middleware('role:Pemilik');

  Route::put('/carts', [CartController::class, 'addOrUpdateItem']);
  Route::get('/carts', [CartController::class, 'get']);
  Route::delete('/carts', [CartController::class, 'deleteCart']);
  Route::delete('/carts/{id}', [CartController::class, 'deleteItems']);
  Route::post('/carts/checkout', [CartController::class, 'checkout']);
});