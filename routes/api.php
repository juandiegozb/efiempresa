<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Product\ProductController;
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

/**
 * Aliases for API Access Controls
 * @author Juan Zambrano <juandiegozb@hotmail.com>
 */
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register'])->name('register');

// Rutas públicas, accesibles sin autenticación
Route::group(['prefix' => 'products'], function () {
    Route::get('/list', [ProductController::class, 'index'])->name('products.index');
});

// Rutas privadas, accesibles SOLO con autenticación
Route::group(['middleware' => ['auth:api'], 'prefix' => 'products'], function () {
    Route::post('/create', [ProductController::class, 'store'])->name('product.store');
    Route::put('/update/{id}', [ProductController::class,'update'])->name('product.update');
    Route::delete('/destroy/{id}', [ProductController::class,'destroy'])->name('product.destroy');
});


Route::group(['middleware' => ['auth:api']], function () {
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
});





