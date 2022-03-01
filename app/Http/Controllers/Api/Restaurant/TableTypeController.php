<?php

namespace App\Http\Controllers\Api\Restaurant;

use App\Http\Controllers\Controller;
use App\Http\Requests\TableTypeRequest;
use App\Models\TableType;
use Illuminate\Http\Request;

class TableTypeController extends Controller
{
    public function index($id=null){
        if($id){
            return TableType::where('restaurant_id')->get();
        }

        if(auth()->user()->user_type == 1)
            return TableType::paginate(6);
    }

    public function storeUpdate(TableTypeRequest $request, $id=null)
    {
        if ($id) {
            try{

                $input = $request->validate();
                $input['restaurant_id'] = auth()->user()->restaurant_id;
                $restaurant = TableType::find($id)
                ->update( $input );

                return \Response::success($restaurant, 'Table Type Updated Successfully');

            }catch(\Exception $e) {
                \Response::failed($e, 'Table Type Updated failed');
            }
        }

        try{

            $input = $request->validate();
            $input['restaurant_id'] = auth()->user()->restaurant_id;
            $restaurant = TableType::create($input);

            return \Response::success($restaurant, 'Table Type  Updated Successfully');

        }catch(\Exception $e) {
            \Response::failed($e, 'Table Type  Updated failed');
        }
    }

    public function destroy($id) {
        try{
            $restaurant =  TableType::destroy($id);
            return \Response::success($restaurant, 'Table Type  deleted Successfully');
        }catch(\Exception $e) {
            \Response::failed($e, 'Table Type delete failed');
        }
    }
}
