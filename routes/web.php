<?php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SearchController;
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

Route::get("/payment/{payment:code}", [PaymentController::class, "show"])->name("payment.show");

Route::prefix("/{service}")->group(function () {
    Route::get("/", [ServiceController::class, "show"])->name("services.show");

    Route::prefix('/{product}')->group(function () {
        Route::get("/payment", [PaymentController::class, "create"])->name("payment.create");
        Route::post("/payment", [PaymentController::class, "store"])->name("payment.store");

        Route::get("/search", [SearchController::class, "index"])->name("search.index");
        Route::post("/search", [SearchController::class, "search"])->name("search.search");
    })
    ->scopeBindings();
});
