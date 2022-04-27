<?php

namespace App\Http\Controllers\Api\Restaurant;

use App\Http\Controllers\Controller;
use App\Http\Requests\TableTypeRequest;
use App\Models\TableType;
use Illuminate\Http\Request;

class TableTypeController extends Controller
{
    public function index($id = null)
    {
        // $restaurant_id = auth()->user()->restaurant_id;

        // $restaurant_tables = TableType::where('restaurant_id', $restaurant_id);

        // return  $restaurant_tables->paginate(6);

        if ($id) {
            return TableType::where('restaurant_id', $id)->get();
        }

        if (auth()->user()->user_type == 2)
            return TableType::where('restaurant_id', auth()->user()->restaurant_id)->get();

    }

    public function store(TableTypeRequest $request, $id = null)
    {
        if (auth()->user()->user_type == 1 || auth()->user()->user_type == 2) {
       
            if (auth()->user()->restaurant_id) {
                try {
                    $table_type = new TableType;
                    $table_type->title = $request->input('title');
                    $table_type->detail = $request->input('detail');
                    $table_type->status = $request->input('status');
                    $table_type->restaurant_id = auth()->user()->restaurant_id;
                    $table_type->save();
                    return response()->json(['message' => 'Table Type Created Successfully ']);
                } catch (Exception $e) {
                    return response()->json(['message' => 'Failed']);
                }
            }

            // for super admin
            if($id){
                    try {
                        $table_type = new TableType;
                        $table_type->title = $request->input('title');
                        $table_type->detail = $request->input('detail');
                        $table_type->status = $request->input('status');
                        $table_type->restaurant_id = $id;
                        $table_type->save();
                        return response()->json(['message' => 'Table Type Created Successfully ']);
                    } catch (Exception $e) {
                        return response()->json(['message' => 'Failed']);
                    }
            }   
        }
     
    }

    public function update(TableTypeRequest $request, $id)
    {
        # code...
        if (auth()->user()->user_type == 1 || auth()->user()->user_type == 2) {
            if ($id) {
                try {

                    $table_type = TableType::find($id);
                    $table_type->title = $request->input('title');
                    $table_type->detail = $request->input('detail');
                    $table_type->status = $request->input('status');
                  
                    $table_type->update();
                    return response()->json(['message' => 'Table Type Updated Successfully ']);
                } catch (Exception $e) {
                    return response()->json(['message' => 'Failed']);
                }
            }
         }
    }

    public function destroy($id)
    {
        try {
            $restaurant =  TableType::destroy($id);
            return \Response::success($restaurant, 'Table Type  deleted Successfully');
        } catch (\Exception $e) {
            \Response::failed($e, 'Table Type delete failed');
        }
    }

    public function getTableTypeDetailsById($id=null)
    {
        // $tb = TableType::find($id);
        // return $tb;

        if ($id) {
            $tb = TableType::find($id);
            return $tb;
        }

        $restaurant_id = auth()->user()->restaurant_id;

        $restaurant_tables = TableType::where('restaurant_id', $restaurant_id);

        return  $restaurant_tables->paginate(6);
    }
}
