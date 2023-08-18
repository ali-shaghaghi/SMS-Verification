<?php

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

Route::get('/', function () {
    return view('welcome');
})->name('index');

Route::get('/login-phone', [\App\Http\Controllers\AuthController::class, 'loginPhone'])->name('loginPhone');
Route::get('/login-email', [\App\Http\Controllers\AuthController::class, 'loginEmail'])->name('loginEmail');
Route::get('/register', [\App\Http\Controllers\AuthController::class, 'register'])->name('register');
Route::post('/register', [\App\Http\Controllers\AuthController::class, 'doRegister'])->name('doRegister');
Route::post('/login-phone', [\App\Http\Controllers\AuthController::class, 'doLoginPhone'])->name('doLoginPhone');
Route::post('/login-email', [\App\Http\Controllers\AuthController::class, 'doLoginEmail'])->name('doLoginEmail');
Route::get('/verify', [\App\Http\Controllers\AuthController::class, 'verify'])->name('verify');
Route::post('/doVerify', [\App\Http\Controllers\AuthController::class, 'doVerify'])->name('doVerify');
Route::get('/logout', [\App\Http\Controllers\AuthController::class, 'logout'])->name('logout');
