<?php

use App\Http\Controllers\Api\Reservation\ReservationController;
use App\Http\Controllers\Api\Restaurant\RestaurantController;
use App\Http\Controllers\Api\Restaurant\TableController;
use App\Http\Controllers\Api\Restaurant\TableTypeController;
use App\Http\Controllers\Api\UserController;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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


Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);

Route::post('filled-reservation/{id?}', [ReservationController::class, 'storeUpdate']);
Route::get('get-reservation/{email}', [ReservationController::class, 'getReservation']);

// open api
Route::get('table-types/{id?}', [TableTypeController::class, 'index']);


Route::group(['middleware' => 'auth:sanctum'], function () {

    // user related
    Route::get('/user', [UserController::class, 'index']);
    Route::get('/logout', [UserController::class, 'logout']);

    // restaurant related
    Route::get('restaurants', [RestaurantController::class, 'index']);
    Route::post('restaurant/{id?}', [RestaurantController::class, 'storeUpdate']);
    Route::post('status', [TableController::class, 'statusChange']);
    Route::post('search-user-phone', [ReservationController::class, 'getUserFmPhone']);
    Route::get('get-users/{restaurant_id}', [UserController::class, 'getUser']);

    // reservation related
    Route::get('reservation/{id?}', [ReservationController::class, 'index']);
    Route::post('checkout/{id?}', [TableController::class, 'checkout']);
    Route::get('checkout-one/{id}/{user_id}', [TableController::class, 'checkoutOne']);
    Route::delete('delete/{id?}', [ReservationController::class, 'destroy']);

});
