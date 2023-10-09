<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Ecommerce\InventoryRequestController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::get('/products/list', [ProductController::class, 'getList']);
Route::get('/products/categories', [ProductController::class, 'getCategories']);
Route::get('/products/{id}/reviews', [ProductController::class, 'getReviews']);
Route::post('/products/reviews/submit', [ProductController::class, 'submitReview']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
