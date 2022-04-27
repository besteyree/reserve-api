<?php

namespace App\Http\Controllers\Api\Restaurant;

use App\Http\Controllers\Controller;
use App\Http\Requests\FloorRequest;
use App\Models\Floor;
use Illuminate\Http\Request;

class FloorController extends Controller
{
    public function index($id = null)
    {
        
        if ($id) {
            return Floor::where('restaurant_id', $id)->get();
        }

        if (auth()->user()->user_type == 2)
       { 
            return Floor::where('restaurant_id', auth()->user()->restaurant_id)->get();
    
        }  
    
    }

    public function store(FloorRequest $request, $id = null)
    {

        if (auth()->user()->user_type == 1 || auth()->user()->user_type == 2) {
            //for admin
            if (auth()->user()->restaurant_id) {
                try {
                    $floor = new Floor;
                    $floor->title = $request->input('title');
                    $floor->status = $request->input('status');
                    $floor->restaurant_id = auth()->user()->restaurant_id ;
                    $floor->save();
                    return response()->json(['message' => 'Floor Created Successfully ']);
                } catch (Exception $e) {
                    return response()->json(['message' => 'Failed']);
                }
            }
            // for super admin
            if($id){
                try {
                    $floor = new Floor;
                    $floor->title = $request->input('title');
                    $floor->status = $request->input('status');
                    $floor->restaurant_id = $id;
                    $floor->save();
                    return response()->json(['message' => 'Floor Created Successfully ']);
                } catch (Exception $e) {
                    return response()->json(['message' => 'Failed']);
                }
            }  
        }
    }

    public function update(FloorRequest $request, $id = null)
    {
        # code...
        if (auth()->user()->user_type == 1 || auth()->user()->user_type == 2) {
            // for admin and super admin 
            if ($id) {
                try {
                    $floor = Floor::find($id);
                    $floor->title = $request->input('title');
                    $floor->status = $request->input('status');
                    $floor->update();
                    return response()->json(['message' => 'Floor Updated Successfully ']);
                } catch (Exception $e) {
                    return response()->json(['message' => 'Failed']);
                }
            }
         }
    }

    public function destroy($id)
    {
        try {
            $restaurant =  Floor::destroy($id);
            return \Response::success($restaurant, 'Floor deleted Successfully');
        } catch (\Exception $e) {
            \Response::failed($e, 'Floor delete failed');
        }
    }

    // public function getFloorDetailsById($id)
    // {
    //     $floor = Floor::find($id);
    //     return $floor;
    // }


    public function getFloorDetailsById($id = null)
    {
        if ($id) {
            return Floor::find($id);
        }

        $restaurant_id = auth()->user()->restaurant_id;

        $restaurant_tables = Floor::where('restaurant_id', $restaurant_id);

        return  $restaurant_tables->paginate(6);
    }
}
