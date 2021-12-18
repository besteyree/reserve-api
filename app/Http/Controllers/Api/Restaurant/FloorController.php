<?php

namespace App\Http\Controllers\Api\Restaurant;

use App\Http\Controllers\Controller;
use App\Http\Requests\FloorRequest;
use App\Models\Floor;
use Illuminate\Http\Request;

class FloorController extends Controller
{
    public function index($id=null){
        if($id){
            return Floor::where('restaurant_id')->get();
        }

        if(auth()->user()->user_type == 1)
            return Floor::get();
    }

    public function storeUpdate(FloorRequest $request, $id=null)
    {
        if ($id) {
            try{

                $input = $request->validate();
                $input['restaurant_id'] = auth()->user()->restaurant_id;
                $restaurant = Floor::find($id)
                ->update( $input );

                return \Response::success($restaurant, 'Floor Updated Successfully');

            }catch(\Exception $e) {
                \Response::failed($e, 'Floor Updated failed');
            }
        }

        try{

            $input = $request->validate();
            $input['restaurant_id'] = auth()->user()->restaurant_id;
            $restaurant = Floor::create($input);

            return \Response::success($restaurant, 'Floor Updated Successfully');

        }catch(\Exception $e) {
            \Response::failed($e, 'Floor Updated failed');
        }
    }

    public function destroy($id) {
        try{
            $restaurant =  Floor::destroy($id);
            return \Response::success($restaurant, 'Floor deleted Successfully');
        }catch(\Exception $e) {
            \Response::failed($e, 'Floor delete failed');
        }
    }
}
