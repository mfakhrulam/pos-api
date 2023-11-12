<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\OutletController;
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

Route::post('/users/login', [UserController::class, 'login']);  

Route::middleware(ApiAuthMiddleware::class)->group(function() {
  Route::get('/users/current', [UserController::class, 'get']);
  Route::patch('/users/current', [UserController::class, 'update']);
  Route::delete('/users/logout', [UserController::class, 'logout']);

  Route::post('/outlets', [OutletController::class, 'create']);
  Route::get('/outlets', [OutletController::class, 'getAll']);
  Route::get('/outlets/{id}', [OutletController::class, 'get'])->where('id', '[0-9]+');
  Route::put('/outlets/{id}', [OutletController::class, 'update'])->where('id', '[0-9]+');
  Route::delete('/outlets/{id}', [OutletController::class, 'delete'])->where('id', '[0-9]+');
  
  Route::post('/employees', [EmployeeController::class, 'create']);
  Route::get('/employees/{id}', [EmployeeController::class, 'get'])->where('id', '[0-9]+');
  Route::put('/employees/{id}', [EmployeeController::class, 'update'])->where('id', '[0-9]+');

});