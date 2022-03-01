<?php

namespace App\Http\Controllers\Api\Restaurant;

use App\Http\Controllers\Controller;
use App\Http\Requests\RestaurantRequest;
use App\Models\Restaurant;
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
}
