<?php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::get('/', [ServiceController::class, "index"])->name("service.index");
Route::get("/{service}", [ServiceController::class, "show"])->name("services.show");

Route::get("/payment/{payment:code}", [PaymentController::class, "show"])->name("payment.show");
Route::get("/{service}/{product}/payment", [PaymentController::class, "create"])->name("payment.create");
Route::post("/{service}/{product}/payment", [PaymentController::class, "store"])->name("payment.store");