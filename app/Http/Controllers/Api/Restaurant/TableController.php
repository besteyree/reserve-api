<?php

namespace App\Http\Controllers\Api\Restaurant;

use App\Http\Controllers\Controller;
use App\Http\Requests\TableRequest;
use App\Jobs\SendConfirmEmail;
use App\Models\FilledReservation;
use App\Models\Restaurant;
use App\Models\Table;
use Illuminate\Http\Request;

class TableController extends Controller
{
    public function index($id=null)
    {
        if ($id) {
            return Table::where('restaurant_id', $id)->paginate(6);
        }

        return Table::paginate(6);
    }

    public function storeUpdate(TableRequest $request, $id=null)
    {
        if ($id) {
            try{

                $input = $request->validate();
                $input['restaurant_id'] = auth()->user()->restaurant_id;
                $restaurant = Table::find($id)
                ->update( $input );

                return \Response::success($restaurant, 'Restaurant Updated Successfully');

            }catch(\Exception $e) {
                \Response::failed($e, 'Restaurant Updated failed');
            }
        }

        try{

            $input = $request->validate();
            $input['restaurant_id'] = auth()->user()->restaurant_id;
            $restaurant = Table::create($input);

            return \Response::success($restaurant, 'Restaurant Updated Successfully');

        }catch(\Exception $e) {
            \Response::failed($e, 'Restaurant Updated failed');
        }
    }

    public function destroy($id) {
        try{
            $restaurant =  Table::destroy($id);
            return \Response::success($restaurant, 'Table deleted Successfully');
        }catch(\Exception $e) {
            \Response::failed($e, 'Table delete failed');
        }
    }

    public function statusChange(Request $request) {
        try{

            foreach($request->table as $table) {
                Table::find($table)
                ->update([
                    'status' => $request->status,
                    'user_id' => $request->user_id,
                ]);
            }

            $user = FilledReservation::find($request->user_id);

            if($user->status === 0){
                dispatch(new SendConfirmEmail($user));
            }

            $user->update([
                'status' => 2,
                'confirmed_date' => now()
            ]);

            return \Response::success(Restaurant::with('floor:id,title,restaurant_id', 'floor.table:id,title,no_of_occupany,floor_id,type_id,status,user_id', 'floor.table.tableType:id,title')->find(1), 'Table Reserved Successfully');

        }catch(\Exception $e) {
            \Response::failed($e, 'Table Reserved failed');
        }
    }

    public function checkout($id)
    {
        try{
            $tableId = Table::where('user_id', $id)->pluck('id');

            Table::whereIn('id', $tableId)->update([
                'status' => 0,
                'user_id' => null
            ]);

            FilledReservation::find($id)
            ->update([
                'status' => 5
            ]);

            return \Response::success([
                'rmTable' => Table::whereIn('id', $tableId)->get(),
                'restaurant' => Restaurant::with('floor:id,title,restaurant_id', 'floor.table:id,title,no_of_occupany,floor_id,type_id,status,user_id', 'floor.table.tableType:id,title')->find(1)
            ], 'Checkout Success');
        }catch(\Exception $e) {
            return \Response::failed($e, 'Checkout failed');
        }

    }
}
