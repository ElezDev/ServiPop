<?php

use App\Http\Controllers\FavoriteController;
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


Route::apiResource('service-providers', ServiceProviderController::class)->middleware('auth:api');
Route::get('services-category/{idCategory}', [ServiceController::class, 'serviceByCategory']);


Route::middleware('auth:api')->group(function () {
    // CRUD básico
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites', [FavoriteController::class, 'store']);
    Route::get('/favorites/{favorite}', [FavoriteController::class, 'show']);
    Route::delete('/favorites/{favorite}', [FavoriteController::class, 'destroy']);
    
    // Funcionalidades de checked
    Route::put('/favorites/{favorite}/check', [FavoriteController::class, 'markAsChecked']);
    Route::put('/favorites/{favorite}/uncheck', [FavoriteController::class, 'unmarkAsChecked']);
    Route::put('/favorites/{favorite}/toggle', [FavoriteController::class, 'toggleChecked']);
    Route::get('/favorites/checked/list', [FavoriteController::class, 'checkedFavorites']);
    
    // Utilidades
    Route::get('/services/{service}/check-favorite', [FavoriteController::class, 'checkFavorite']);
    Route::post('/favorites/bulk-update', [FavoriteController::class, 'bulkUpdateCheckedStatus']);
});