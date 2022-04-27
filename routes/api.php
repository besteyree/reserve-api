<?php

use App\Http\Controllers\Api\Reservation\ReservationController;
use App\Http\Controllers\Api\Restaurant\RestaurantController;
use App\Http\Controllers\Api\Restaurant\TableController;
use App\Http\Controllers\Api\Restaurant\TableTypeController;
use App\Http\Controllers\Api\Restaurant\SmsMessageController;
use App\Http\Controllers\Api\Restaurant\FloorController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\Restaurant\RestaurantStatisticsController;
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


Route::post('register/{id?}', [UserController::class, 'register']);
Route::post('vendor_update/{id}', [UserController::class, 'update']);
Route::post('login', [UserController::class, 'login']);

// open api
Route::post('filled-reservation/{id?}', [ReservationController::class, 'storeUpdate']);
//reservation for customer
Route::post('customer_reservation', [ReservationController::class, 'customer_reservation']);


Route::get('get-reservation/{email}', [ReservationController::class, 'getReservation']);
Route::get('table-types/{id?}', [TableTypeController::class, 'index']);

//save token
Route::post('token-save', [ReservationController::class, 'saveToken']);

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
    Route::get('analytic', [RestaurantController::class, 'analytic']);
    
    // reservation related
    Route::post('restaurants/filled-reservation/{id?}', [ReservationController::class, 'storeUpdate']);
    Route::get('reservation/{id?}', [ReservationController::class, 'index']);
    Route::post('checkout/{id?}', [TableController::class, 'checkout']);
    Route::get('checkout-one/{id}/{user_id}', [TableController::class, 'checkoutOne']);
    Route::post('delete/{id?}', [ReservationController::class, 'destroy']);
    Route::get('restore/{id}', [ReservationController::class, 'restore']);
    Route::get('walkin', [ReservationController::class, 'walkin']);
    Route::get('default', [ReservationController::class, 'default']);
    Route::get('all/', [ReservationController::class, 'all']);
    Route::get('deleted', [ReservationController::class, 'deleted']);
    Route::get('left', [ReservationController::class, 'left']);


      //restuarant admin and Super admin  api
    // SMS messages
    Route::get('sms_messages/{id?}', [SmsMessageController::class, 'index']);
    Route::post('sms_messages_store/{id?}', [SmsMessageController::class, 'store']);
    Route::post('sms_messages_update/{id?}', [SmsMessageController::class, 'update']);
    Route::get('sms_messages_delete/{id}', [SmsMessageController::class, 'destroy']);

    //Floor
    Route::get('floor/{id?}', [FloorController::class, 'index']);
    Route::post('floor_store/{id?}', [FloorController::class, 'store']);
    Route::post('floor_update/{id?}', [FloorController::class, 'update']);
    Route::get('floor_delete/{id}', [FloorController::class, 'destroy']);
    Route::get('get_floor/{id?}', [FloorController::class, 'getFloorDetailsById']);

    //table types
    Route::get('table_type/{id?}', [TableTypeController::class, 'index']);
    Route::post('table-types_store/{id?}', [TableTypeController::class, 'store']);
    Route::post('table-types_update/{id?}', [TableTypeController::class, 'update']);
    Route::get('table-types_delete/{id?}', [TableTypeController::class, 'destroy']);
    Route::get('get_table_type/{id?}', [TableTypeController::class, 'getTableTypeDetailsById']);


    // vendor(admin)
    Route::get('getvendor/{id?}', [UserController::class, 'getvendor']);


    Route::get('getvendor_reservation', [ReservationController::class, 'getvendor_reservation']);
    
    
    //table generate
    Route::post('tablegenerrate/{id?}', [TableController::class, 'resttable']);
    Route::get('get_table/{id?}', [TableController::class, 'getTableDetailsById']);

    //get all the restaurants
    Route::get('getvendor_restaurant/{id?}', [RestaurantController::class, 'getvendor_restaurant']);

    Route::post('getvendor_restaurant_store_update/{id?}', [RestaurantController::class, 'getvendor_restaurant_store_update']);



    
    //restaurant_statistics
    Route::get('restaurant_statistics/{id?}', [RestaurantStatisticsController::class, 'restaurant_statistics']);


    // list walkin
    Route::post('is-walking', [ReservationController::class, 'reservation_is_walking']);
    Route::post('is-reservation', [ReservationController::class, 'reservation_is']);


  });
  
