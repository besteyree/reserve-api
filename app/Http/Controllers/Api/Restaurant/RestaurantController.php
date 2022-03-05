<?php

namespace App\Http\Controllers\Api\Restaurant;

use App\Http\Controllers\Controller;
use App\Http\Requests\RestaurantRequest;
use App\Models\FilledReservation;
use App\Models\Restaurant;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    public function index()
    {
        if(auth()->user()->user_type == 1){

            return Restaurant::with('floor', 'tableType', 'table')
            ->paginate(6);
        }

        return Restaurant::with('floor', 'tableType', 'table')
        ->where('user_id', auth()->id())->paginate(6);
    }

    public function storeUpdate(RestaurantRequest $request, $id=null)
    {

        if($id){
            try{
                $restaurant = Restaurant::find($id)
                ->update($request->validate());

                $restaurant->location()->create($request->validate());

                return \Response::success($restaurant, 'Restaurant Updated Successfully');
            }catch(\Exception $e) {
                \Response::failed($e, 'Restaurant Updated failed');
            }
        }

        try{

            $restaurant = Restaurant::create($request->validate());

            $restaurant->location()->create($request->validate());

            return \Response::success($restaurant, 'Restaurant Created Successfully');
        }catch(\Exception $e) {
            \Response::failed($e, 'Restaurant Created failed');
        }
    }

    public function destroy($id) {
        try{
            $restaurant =  Restaurant::destroy($id);
            return \Response::success($restaurant, 'Restaurant deleted Successfully');
        }catch(\Exception $e) {
            \Response::failed($e, 'Restaurant deleted failed');
        }

    }

    public function analytic()
    {

        $from = date(\Request('from'));
        $to = date(\Request('to'));
        $reservation = FilledReservation::query();

        if(\Request('from') && \Request('to'))
            $reservation->whereBetween('date', [$from, $to]);

        if(!\Request('from') && !\Request('to')){
            $reservation->whereDate('date', Carbon::today());
        }

        if(\Request('from') && !\Request('to')) {
            $reservation->whereDate('date', \Request('from') );
        }

        $reservation->where('is_walkin', '=', null);


        $walkin = FilledReservation::query();

        $walkin->where('is_walkin', '=', 1);

        if(\Request('from') && \Request('to'))
            $walkin->whereBetween('date', [$from, $to]);

        if(!\Request('from') && !\Request('to')){
            $walkin->whereDate('date', Carbon::today());
        }

        if(\Request('from') && !\Request('to')) {
            $walkin->whereDate('date', \Request('from') );
        }

        return [
            'reservation' => $reservation->sum('no_of_occupancy'),
            'walkin' => $walkin->sum('no_of_occupancy')
        ];
    }
}
