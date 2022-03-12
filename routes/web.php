<?php

use App\Models\FilledReservation;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    $reservation = FilledReservation::query();
    $reservation->selectRaw("str_to_date(time,'%h:%i %p') as f_time");
    $reservation->orderBy('f_time', 'DESC');
    return $reservation->get();
});

Route::get('/email', function () {
    return view('email.confirmed');
});
