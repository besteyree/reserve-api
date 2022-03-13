<?php

use App\Models\FilledReservation;
use Carbon\Carbon;
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
    $reservation->select('phone','time','id');
    $reservation->selectRaw("str_to_date(time,'%h:%i %p') as f_time");
    $reservation->where('time', '!=', '');
    $reservation->orderBy('f_time', 'DESC');
    $reservation->where('phone', '8888888888');
    return $reservation->get()->where('f_time', '<', Carbon::now()->subHours(8)->toDateTimeString())->count();

});

Route::get('/email', function () {
    return view('email.confirmed');
});
