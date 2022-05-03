<?php

namespace App\Http\Controllers\Api\Restaurant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RestaurantStatisticsController extends Controller
{
    public function restaurant_statistics(Request $request, $id = null)
    {
        # code...
        // yesterday Date (formate : Y.M.D)
        // $date = date('Y-m-d', strtotime("-1 days"));

        $appends = ['reservation_seated', 'reservation', '$is_walkin ', 'reservation_repeated','total_seated'];
        
       

        $date = $request->d;

        //reservation 
        $data =  DB::table('filled_reservations')
            ->where("date", "=", $date)
            ->where("restaurant_id","=",auth()->user()->restaurant_id)
            ->get();

        //count reservation        
        $reservation = $data->count();

        //reservation seated
        $data2 = DB::table('filled_reservations')
            ->select(DB::raw('DATE(seated_date) AS seated_date '))
            //  ->where("seated_date","=", $date)
            ->whereNotNull('seated_date')
            ->where("restaurant_id","=",auth()->user()->restaurant_id)
            ->get();

        //count reservation seated           
        $reservation_seated   = 0;
        foreach ($data2  as $value) {
            # code...
            if ($value->seated_date == $date) {

                $reservation_seated++;
            }

            $reservation_seated  = $reservation_seated;

            # code...

        }

        //is walkin
        $data3 = DB::table('filled_reservations')
            ->where('is_walkin', '=', 1)
            ->where("date", "=", $date)
            ->where("restaurant_id","=",auth()->user()->restaurant_id)
            ->get();

        $is_walkin = $data3->count();


        // reservation repeated
        $data3 = DB::table('filled_reservations')
            ->select(DB::raw(' COUNT(phone) as repeated, phone'))
            ->where("date", "=", $date)
            ->where("restaurant_id","=",auth()->user()->restaurant_id)
            ->groupBy('phone')
            ->get();

        $reservation_repeated = 0;
        $phone = "0000000";

        foreach ($data3 as $value) {
            # code...    

            if ($value->repeated > 1) {

                $str = strcmp($phone, $value->phone);

                if ($str != 0) {
                    # code...
                    $reservation_repeated =  $reservation_repeated + 1;
                    $phone =  $value->phone;
                }

                $phone =  $value->phone;
            }
            $reservation_repeated = $reservation_repeated;
        }


        //Total seated
        $total_seated = $is_walkin + $reservation_seated;


        return [
            'reservation' => $reservation,
            'reservation_seated' => $reservation_seated,
            'is_walkin' => $is_walkin,
            'reservation_repeated' => $reservation_repeated,
            'total_seated'=>  $total_seated 

        ];
    }


    public function reservation_isWalking($id = null)
    {
        # code...

     $iswalking = DB::table('filled_reservations')
        ->where('is_walkin', '=', 1)
        ->where("date", "=", $date)
        ->where("restaurant_id","=",auth()->user()->restaurant_id)
        ->get();

        return $iswalking;
    }

}
