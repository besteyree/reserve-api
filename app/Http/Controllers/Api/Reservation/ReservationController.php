<?php

namespace App\Http\Controllers\Api\Reservation;

use App\Http\Controllers\Controller;
use App\Http\Requests\FilledReservationRequest;
use App\Jobs\SendConfirmEmail;
use App\Models\FilledReservation;
use App\Models\DeviceToken;
use App\Models\Restaurant;
use App\Models\Table;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class ReservationController extends Controller
{

    public function getReservation($email)
    {
        return FilledReservation::where('email', $email)->first();
    }

    public function index($id=null)
    {
        if($id){
            $reservation = FilledReservation::query();
            $reservation->where('restaurant_id', $id);
            $search = \Request('filter');

            if(\Request('page') == 'todayTomorrow'){

                $reservation
                ->select('date', \DB::raw('count(*) as total'))
                ->whereDate('date', '>=', Carbon::today())
                ->whereIn('status', ['0', '2']);
                return $reservation->groupBy('date')
                ->get();

            }

            if(\Request('page') == 'history'){

                $reservation
                ->select('date', \DB::raw('count(*) as total'))
                ->whereIn('status',[4, 5]);

                return $reservation
                ->groupBy('date')
                ->get();

            }

            if(\Request('day') == 'history'){
                if( \Request('filter'))
                    $reservation->where('name','LIKE' ,"%$search%");

                $reservation
                ->withTrashed()
                ->where(function($row) {
                    $row->whereIn('status', ['0', '1', '2', '3', '4', '5']);
                    $row->orWhere('deleted_at', '!=', null);
                });
                $reservation->orderBy('created_at', 'DESC');
                return $reservation->paginate(10);
            }

            if(in_array(\Request('status'), ['0', '2', '3'])){
                if( \Request('filter'))
                    $reservation->where('name','LIKE' ,"%$search%");

                $reservation->where('status', \Request('status'));
            }

            if(\Request('day') == 'waitlisted'){

                if( \Request('filter'))
                    $reservation->where('name', 'LIKE' ,"%$search%");

                if(\Request('wait_to')){
                    if(\Request('wait_from') == '8'){
                        $reservation->where('no_of_occupancy', '>', 8)
                        ->where(function($row) {
                            $row->where('status', 1);
                            $row->orWhere('type', 2);
                        });
                    }else{
                        $reservation->where('no_of_occupancy', '>', \Request('wait_from'))
                        ->where('no_of_occupancy', '<', \Request('wait_to'))
                        ->where(function($row) {
                            $row->where('status', 1);
                            $row->orWhere('type', 2);
                        });
                    }

                }

                $reservation
                ->where(function($row) {
                    $row->where('status', 1);
                    $row->orWhere('type', 2);
                })
                ->orderBy('created_at', 'DESC');
                return  $reservation->paginate(6);
            }

            if(!in_array(\Request('day'), ['today', 'tomorrow'])){
                if(\Request('status') == 'is_day')
                {
                    if(\Request('filter'))
                        $reservation->where('name','LIKE' ,"%$search%");

                    $reservation->whereIn('status', ['0', '2']);

                    $reservation->whereDay('date', date('d', strtotime(\Request('day'))))
                    ->whereMonth('date', date('m', strtotime(\Request('day'))))
                    ->whereYear('date', date('Y', strtotime(\Request('day'))));

                    return $reservation->get();
                }

                if(\Request('status') == 'is_history')
                {
                    if( \Request('filter'))
                        $reservation->where('name','LIKE' ,"%$search%");

                    $reservation->whereIn('status', [4, 5])
                    ->whereDay('date', date('d', strtotime(\Request('day'))))
                    ->whereMonth('date', date('m', strtotime(\Request('day'))))
                    ->whereYear('date', date('Y', strtotime(\Request('day'))));

                    $reservation->orderBy('seated_date', 'DESC');
                    return $reservation->get();
                }

                if(\Request('status') == 'is_history_cal')
                {
                    $reservation->where('status', 4)
                    ->whereMonth('date', date('m', strtotime(\Request('day'))) + 1)
                    ->whereYear('date', date('Y', strtotime(\Request('day'))));

                    return $reservation->get();
                }

                $reservation
                ->select('id', 'date', 'time', 'name', 'status')
                ->where('status', '!=', 5)
                ->where('status', '!=', 4)
                ->whereMonth('date', date('m', strtotime(\Request('day'))) + 1)
                ->whereYear('date', date('Y', strtotime(\Request('day'))));
                return $reservation->get();
            }


            if(\Request('day') == 'today'){

                if( \Request('filter'))
                    $reservation->where('name','LIKE' ,"%$search%");

                $reservation->whereDate('date', Carbon::today());
                $reservation->where('type', null);

                if(in_array(\Request('status'), ['0', '2']))
                    $reservation->where('status', \Request('status') == 0 ? '0' : \Request('status'));
                else
                    $reservation->whereIn('status', ['0', '2']);

                $reservation->orderBy('date', 'DESC');
                return $reservation->paginate(6);
            }

            if(\Request('day') == 'tomorrow'){
                if( \Request('filter'))
                    $reservation->where('name','LIKE' ,"%$search%");

                $reservation->where('type', null);

                if(in_array(\Request('status'), ['0', '2']))
                    $reservation->where('status', \Request('status') == 0 ? '0' : \Request('status'));
                else
                    $reservation->whereIn('status', ['0', '2']);

                $reservation->whereDate('date', Carbon::tomorrow())
                ->orderBy('date', 'DESC');
            }

            return $reservation->paginate(6);
        }

        if(auth()->user()->user_type == 1)
            return FilledReservation::paginate(6);
    }

    public function storeUpdate(FilledReservationRequest $request, $id=null)
    {

        if ($id) {
            try{
                $input = $request->validated();

                if($input['status'] == 4){
                    $input['seated_date'] = now();
                }

                FilledReservation::find($id)
                ->update( $input );

                if($input['status'] == 2){
                    dispatch(new SendConfirmEmail(FilledReservation::find($id)));
                }

                return \Response::success(Restaurant::with('floor:id,title,restaurant_id', 'floor.table:id,title,no_of_occupany,floor_id,type_id,status,user_id', 'floor.table.tableType:id,title', 'floor.table.user')->find(1), 'Reservation Updated Successfully');

            }catch(\Exception $e) {
                return \Response::failed($e, 'Reservation Updated failed');
            }
        }

        try{

            $input = $request->validated();
            $input['restaurant_id'] = auth()->check() ? auth()->user()->restaurant_id : 1;

            $reservation = FilledReservation::create($input);

            if(isset($request->table) && count($request->table ?? 0) > 0){
                foreach($request->table as $table) {
                    Table::find($table)
                    ->update([
                        'status' => $request->status,
                        'user_id' => $reservation->id,
                    ]);
                }

                $user = FilledReservation::find($reservation->id);

                $status = 1;

                if($request->is_walkin == 1)
                {
                    if($request->type == 1)
                    {
                        $status = 4;
                        $user->update([
                            'seated_date' => now(),
                        ]);
                    }
                }else {
                    $status = $request->status;
                }

                $user->update([
                    'status' => $status,
                ]);
            }

            return \Response::success($reservation, "Thank You! Reservation sent for Confirmation.");

        }catch(Exception $e) {
            return \Response::failed($e, 'Reservation Updated failed');
        }
    }

    public function destroy($id, Request $request)
    {
        $reserve = FilledReservation::find($id);
        $reserve->update([
            'delete_reason' => $request->reason
        ]);

        FilledReservation::destroy($id);

        $reservation = FilledReservation::where(function($row) {
            $row->where('status', 1);
            $row->orWhere('type', 2);
        })
        ->orderBy('created_at', 'DESC')
        ->paginate(6);

        return \Response::success(['reservation' => $reservation, 'count' => Restaurant::with('floor:id,title,restaurant_id', 'floor.table:id,title,no_of_occupany,floor_id,type_id,status,user_id', 'floor.table.tableType:id,title', 'floor.table.user')->find(1)], "Reservation Successfully deleted!");
    }

    public function getUserFmPhone(Request $request)
    {
        return FilledReservation::where('phone', $request->phone)
        ->first();
    }

    public function saveToken(Request $request){

        if(!DeviceToken::where('token', $request->token)->exists()){
            DeviceToken::create([
                'device' => $request->device,
                'token' => $request->token,
            ]);
        }

        return 'success';
    }

    public function restore($id)
    {
        FilledReservation::withTrashed()
        ->find($id)->restore();
        return 'Successfully Restored!';
    }

    public function walkin()
    {
        $reservation = FilledReservation::query();
        $reservation->where('restaurant_id', 1);
        $search = \Request('filter');

        if(!empty($search))
            $reservation->where('name','LIKE' ,"%$search%");

        $reservation->where('type', '1')
        ->where('is_walkin', '1')
        ->orderBy('created_at', 'DESC');

        $from = date(\Request('from'));
        $to = date(\Request('to'));

        if(\Request('from') && \Request('to'))
            $reservation->whereBetween('date', [$from, $to]);
        else
            $reservation->whereDate('date', Carbon::today());

        if(\Request('from') && !\Request('to')) {
            $reservation->whereDate('date', \Request('from') );
        }

        return $reservation->paginate(10);
    }

    public function all()
    {
        $reservation = FilledReservation::query();
        $reservation->where('restaurant_id', 1);
        $search = \Request('filter');

        if(!empty($search))
            $reservation->where('name','LIKE' ,"%$search%");

        $reservation->where('is_walkin', null)
        ->orderBy('created_at', 'DESC');

        $reservation->whereNotIn('status', ['0', '2']);

        $from = date(\Request('from'));
        $to = date(\Request('to'));

        if(\Request('from') && \Request('to'))
            $reservation->whereBetween('date', [$from, $to]);
        else
            $reservation->whereDate('date', Carbon::today());

        if(\Request('from') && !\Request('to')) {
            $reservation->whereDate('date', \Request('from') );
        }

        return $reservation
        ->paginate(10);
    }

    public function deleted()
    {
        $reservation = FilledReservation::query();
        $reservation->where('restaurant_id', 1);
        $search = \Request('filter');

        if(!empty($search))
            $reservation->where('name','LIKE' ,"%$search%");

        $reservation
        ->withTrashed()
        ->where('deleted_at', '!=', null)
        ->orderBy('deleted_at', 'DESC');

        return $reservation
        ->paginate(10);
    }

    public function left()
    {
        $reservation = FilledReservation::query();
        $reservation->where('restaurant_id', 1);
        $search = \Request('filter');

        if(!empty($search))
            $reservation->where('name','LIKE' ,"%$search%");

        $reservation
        ->where('status', 5)
        ->orderBy('created_at', 'DESC');

        return $reservation
        ->paginate(10);
    }
}
