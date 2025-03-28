<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\PortfolioImageController;
use App\Http\Controllers\ServiceProviderController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/refresh-token', [AuthController::class, 'refreshToken'])->middleware('auth:api');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
Route::get('/user', [AuthController::class, 'user'])->middleware('auth:api');

Route::apiResource('services', ServiceController::class)->middleware('auth:api');;
Route::apiResource('categories', CategoryController::class);


Route::apiResource('service-providers.portfolio-images', PortfolioImageController::class)
    ->only(['index', 'store', 'show', 'destroy']);


Route::apiResource('service-providers', ServiceProviderController::class)->middleware('auth:api');;