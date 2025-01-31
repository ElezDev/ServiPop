<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ServiceController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
Route::get('/user', [AuthController::class, 'user'])->middleware('auth:api');

Route::apiResource('services', ServiceController::class);
Route::apiResource('categories', CategoryController::class);

use App\Http\Controllers\PortfolioImageController;

Route::apiResource('service-providers.portfolio-images', PortfolioImageController::class)
    ->only(['index', 'store', 'show', 'destroy']);