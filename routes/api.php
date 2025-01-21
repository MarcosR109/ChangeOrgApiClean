<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\PeticioneController;
use App\Http\Controllers\AuthController;
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

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/
/*Route::controller(UserController::class)->group(function(){
    Route::post('register', 'register');
    Route::get('user/{user}', 'show');
    Route::get('user/{user}/address', 'show_address');
    Route::post('users/{user}/events/{event}/book', 'bookEvent');
    Route::get('users/{user}/events', 'listEvents');
});*/
Route::controller(PeticioneController::class)->group(function () {
    Route::get('peticiones', 'index');
    Route::get('peticiones/firmadas', 'listarFirmadas');
    Route::get('peticiones/{id}', 'listMine');
    Route::get('peticiones/show/{id}', 'show');
    Route::put('peticiones/{id}', 'update');
    Route::post('peticiones/store', 'store');
    Route::put('peticiones/estado/{id}', 'cambiarEstado');
    Route::delete('peticiones/delete/{id}', 'delete');
    Route::put('peticiones/firmar/{id}', 'firmar');
});

Route::controller(CategoriaController::class)->group(function () {
    Route::post('categorias', 'store');
    Route::get('categorias/show/{id}', 'show')->middleware('auth:api');
});

Route::controller(AuthController::class)->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');
    Route::get('me', 'me');
});
