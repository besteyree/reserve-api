<?php

namespace App\Http\Controllers\Api\Restaurant;

use App\Http\Controllers\Controller;
use App\Http\Requests\RestaurantRequest;
use App\Models\FilledReservation;
use App\Models\User;
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

    //not working
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
        $reservation->where('restaurant_id', auth()->user()->restaurant_id);

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
        $walkin->where('restaurant_id', auth()->user()->restaurant_id);

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


    //fetch reataurant
    public function getvendor_restaurant($id = null)
    {
        # code...
        if (auth()->user()->user_type == 1 || auth()->user()->user_type == 2) {

            if ($id) {
                return Restaurant::find($id);
            } 

            if(auth()->user()->user_type ==2){
                 return Restaurant::where('user_id', '=' ,auth()->user()->id)->get();
            }

            if(auth()->user()->user_type ==1){
                return Restaurant::paginate(10);

            }
        }
    }

//create and update restaurant
    public function getvendor_restaurant_store_update(Request $request, $id = null)
    {
        # code...
        if (auth()->user()->user_type == 1) {
            if ($id == null) {
                try {
                    $restaurant = new Restaurant;
                    $restaurant->title = $request->input('title');
                    $restaurant->phone = $request->input('phone');
                    $restaurant->additional_phone = $request->input('additional_phone');
                    $restaurant->opening_time = $request->input('opening_time');
                    $restaurant->closing_time = $request->input('closing_time');
                    $restaurant->detail = $request->input('detail');
                    $restaurant->max_table_occupancy = $request->input('max_table_occupancy');
                    $restaurant->status = $request->input('status');
                    $restaurant->user_id = $request->input('user_id');
                    $restaurant->save();
                    // return $restaurant->id;

                    $user = User::find($restaurant->user_id);
                    $user->restaurant_id = $restaurant->id;
                    $user->update(); 
                    
                    return response()->json(['message' => 'Restaurant Created Successfully ']);
                } catch (Exception $e) {
                    return response()->json(['message' => 'Failed']);
                }
            }

            if ($id) {
                try {
                    $restaurant = Restaurant::find($id);
                    $restaurant->title = $request->input('title');
                    $restaurant->phone = $request->input('phone');
                    $restaurant->additional_phone = $request->input('additional_phone');
                    $restaurant->closing_time = $request->input('closing_time');
                    $restaurant->detail = $request->input('detail');
                    $restaurant->max_table_occupancy = $request->input('max_table_occupancy');
                    $restaurant->status = $request->input('status');
                    $restaurant->user_id = $request->input('user_id');
                    $restaurant->update();
                    return response()->json(['message' => 'Restaurant Updated Successfully ']);
                } catch (Exception $e) {
                    return response()->json(['message' => 'Failed']);
                }
            }
        }
    }





    
}
