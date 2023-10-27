<?php

use App\Http\Controllers\Api\CallbackController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ServiceController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::apiResource("service", ServiceController::class)->only("index");
Route::post("product/{uuid}/pay", [ProductController::class, "pay"])->name("product.pay");
Route::get("payment/{code}", [PaymentController::class, "get"])->name("payment.get");
Route::post("callback", CallbackController::class)->name("callback");