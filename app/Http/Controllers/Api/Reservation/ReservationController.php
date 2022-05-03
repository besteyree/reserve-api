<?php

namespace App\Http\Controllers\Api\Reservation;

use App\Http\Controllers\Controller;
use App\Http\Requests\FilledReservationRequest;
use App\Jobs\SendConfirmEmail;
use App\Models\FilledReservation;
use App\Models\DeviceToken;
use App\Models\Restaurant;
use App\Models\Table;
use App\Models\Sms_Message;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use DB;

class ReservationController extends Controller
{

    public function getReservation($email)
    {
        return FilledReservation::where('email', $email)->first();
    }

    public function index($id = null)
    {
        if ($id) {
            $reservation = FilledReservation::query();
            $reservation
                ->select('*')
                ->selectRaw("str_to_date(time,'%h:%i %p') as f_time");

            $reservation->where('restaurant_id', auth()->user()->restaurant_id);

            $search = \Request('filter');

            if (\Request('page') == 'todayTomorrow') {
                $reservation->where('type', null);
                $reservation
                    ->select('date', \DB::raw('count(*) as total'))
                    ->whereDate('date', '>=', Carbon::today())
                    ->whereIn('status', ['0', '2']);

                return $reservation->groupBy('date')
                    ->get();
            }

            if (\Request('page') == 'history') {

                $reservation
                    ->select('date', \DB::raw('count(*) as total'))
                    ->whereIn('status', [4, 5]);

                return $reservation
                    ->groupBy('date')
                    ->get();
            }

            if (\Request('day') == 'history') {
                if (\Request('filter'))
                    $reservation->where('name', 'LIKE', "%$search%");

                $reservation
                    ->withTrashed()
                    ->where(function ($row) {
                        $row->whereIn('status', ['3', '4', '5']);
                        $row->orWhere('deleted_at', '!=', null);
                    });
                $reservation->orderBy('created_at', 'DESC');

                $data['count_pax'] = $reservation->sum('no_of_occupancy');
                $data['reservation'] = $reservation->orderBy('created_at', 'DESC')->paginate(10);

                return $data;
            }

            if (in_array(\Request('status'), ['0', '2', '3'])) {
                if (\Request('filter'))
                    $reservation->where('name', 'LIKE', "%$search%");

                $reservation->where('status', \Request('status'));
            }

            if (\Request('day') == 'waitlisted') {

                if (\Request('filter'))
                    $reservation->where('name', 'LIKE', "%$search%");

                if (\Request('wait_to')) {
                    if (\Request('wait_from') == '8') {
                        $reservation->where('no_of_occupancy', '>', 8)
                            ->where(function ($row) {
                                $row->where('status', 1);
                                $row->orWhere('type', 2);
                            });
                    } else {
                        $reservation->where('no_of_occupancy', '>', \Request('wait_from'))
                            ->where('no_of_occupancy', '<', \Request('wait_to'))
                            ->where(function ($row) {
                                $row->where('status', 1);
                                $row->orWhere('type', 2);
                            });
                    }
                }

                $reservation
                    ->where(function ($row) {
                        $row->where('status', 1);
                        $row->orWhere('type', 2);
                    });
                $reservation->orderBy('f_time', 'DESC');
                return  $reservation->paginate(6);
            }

            if (!in_array(\Request('day'), ['today', 'tomorrow'])) {
                if (\Request('status') == 'is_day') {
                    if (\Request('filter'))
                        $reservation->where('name', 'LIKE', "%$search%");

                    $reservation->where('type', null);
                    $reservation->whereIn('status', ['0', '2']);

                    $reservation->whereDay('date', date('d', strtotime(\Request('day'))))
                        ->whereMonth('date', date('m', strtotime(\Request('day'))))
                        ->whereYear('date', date('Y', strtotime(\Request('day'))));

                    return $reservation->get();
                }

                if (\Request('status') == 'is_history') {
                    if (\Request('filter'))
                        $reservation->where('name', 'LIKE', "%$search%");

                    $reservation->whereIn('status', [4, 5])
                        ->whereDay('date', date('d', strtotime(\Request('day'))))
                        ->whereMonth('date', date('m', strtotime(\Request('day'))))
                        ->whereYear('date', date('Y', strtotime(\Request('day'))));

                    $reservation->orderBy('seated_date', 'DESC');
                    return $reservation->get();
                }

                if (\Request('status') == 'is_history_cal') {
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

            if (\Request('day') == 'today') {

                if (\Request('filter'))
                    $reservation->where('name', 'LIKE', "%$search%");

                $reservation->where('type', null);
                $reservation->whereDate('date', Carbon::today());

                if (in_array(\Request('status'), ['0', '2']))
                    $reservation->where('status', \Request('status') == 0 ? '0' : \Request('status'));
                else
                    $reservation->whereIn('status', ['0', '2']);

                $reserv['paxLunch'] = $reservation->get()->map(function ($row) {
                    $time = date("H:i", strtotime($row->time));
                    $startTime = date("H:i", strtotime('11:30 AM'));
                    $endTime = date("H:i", strtotime('04:29 PM'));

                    if ($time >= $startTime && $time <= $endTime) {
                        return $row->no_of_occupancy;
                    }
                })->filter()->values();

                $reserv['paxDinner'] = $reservation->get()->map(function ($row) {
                    $time = date("H:i", strtotime($row->time));
                    $startTime = date("H:i", strtotime('04:30 PM'));
                    $endTime = date("H:i", strtotime('11:59 PM'));

                    if ($time >= $startTime && $time <= $endTime) {
                        return $row->no_of_occupancy;
                    }
                })->filter()->values();

                $reservation->orderBy('f_time', 'DESC');

                $reserv['all'] = $reservation->paginate(6);

                return $reserv;
            }

            if (\Request('day') == 'tomorrow') {
                if (\Request('filter'))
                    $reservation->where('name', 'LIKE', "%$search%");

                $reservation->where('type', null);

                if (in_array(\Request('status'), ['0', '2']))
                    $reservation->where('status', \Request('status') == 0 ? '0' : \Request('status'));
                else
                    $reservation->whereIn('status', ['0', '2']);

                $reservation->whereDate('date', Carbon::tomorrow())
                    ->orderBy('date', 'DESC');
            }

            return $reservation->paginate(6);
        }

        if (auth()->user()->user_type == 1)
            return FilledReservation::all();
    }

    public function storeUpdate(FilledReservationRequest $request, $id = null)
    {

        if ($id) {
            try {
                $input = $request->validated();


                if (isset($input['status']) && $input['status'] == 4) {
                    $input['seated_date'] = now();
                }

                FilledReservation::find($id)
                    ->update($input);

                return \Response::success(Restaurant::with('floor:id,title,restaurant_id', 'floor.table:id,title,no_of_occupany,floor_id,type_id,status,user_id', 'floor.table.tableType:id,title', 'floor.table.user')->find(1), 'Reservation Updated Successfully');
            } catch (\Exception $e) {
                return \Response::failed($e, 'Reservation Updated failed');
            }
        }

        if ($id == null) {

            try {

                // temparary kept later remove                  
                $restaurant_id = auth()->user()->restaurant_id;

                $input = $request->validated();
                $input['restaurant_id'] = auth()->check() ? auth()->user()->restaurant_id : $restaurant_id;

                // fetch sms crendential from smsTable
                $smsCrendential = Sms_Message::where('restaurant_id', '=', auth()->user()->restaurant_id)
                    ->get();;


                $checkReservation = FilledReservation::select('phone', 'time', 'id')
                    ->selectRaw("str_to_date(time,'%h:%i %p') as f_time")
                    ->where('restaurant_id', auth()->check() ? auth()->user()->restaurant_id : $restaurant_id)
                    ->where('phone', $request->phone)
                    ->get()
                    ->where('f_time', '>', Carbon::now()->subHours(8)->toDateTimeString());

                // if user already reserced before 8 hrs
                if (\Request('ignore') != 1 && $checkReservation->count() > 0) {
                    return \Response::success(false, 'Already Reserved, Still want to take Reservation?');
                }


                $reservation = FilledReservation::create($input);

                if (isset($request->table) && count($request->table ?? 0) > 0) {
                    foreach ($request->table as $table) {
                        Table::find($table)
                            ->update([
                                'status' => $request->status,
                                'user_id' => $reservation->id,
                            ]);
                    }

                    $user = FilledReservation::find($reservation->id);

                    $status = 1;

                    if ($request->is_walkin == 1) {
                        if ($request->type == 1) {
                            $status = 4;
                            $user->update([
                                'seated_date' => now(),
                            ]);
                        }
                    } else {
                        $status = $request->status;
                    }

                    $user->update([
                        'status' => $status,
                    ]);
                }


                // SMS Message 
                if ($request->status == 1) {
                    # code...
                    $this->sms($smsCrendential, $reservation_status = "Pending ", $request);
                }

                if ($request->status == 2) {
                    $this->sms($smsCrendential, $reservation_status = "Confirmed ", $request);
                }

                if ($request->status == 4) {
                    $this->sms($smsCrendential, $reservation_status = "seated now ", $request);
                }
                // else{   
                //     $this->sms($smsCrendential, $reservation_status = "Reservation Confirmed",$request);
                // }


                return \Response::success($reservation, "Thank You! Reservation sent for Confirmation.");
            } catch (Exception $e) {
                return \Response::failed($e, 'Reservation Updated failed');
            }
        }
    }



    public function destroy($id, Request $request)
    {
        $reserve = FilledReservation::find($id);
        $reserve->update([
            'delete_reason' => $request->reason
        ]);

        FilledReservation::destroy($id);

        $reservation = FilledReservation::where(function ($row) {
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
            ->where('restaurant_id', auth()->user()->restaurant_id)
            ->first();;
    }

    public function saveToken(Request $request)
    {

        if (!DeviceToken::where('token', $request->token)->exists()) {
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
        $search = \Request('filter');
        $data = collect();
        $from = date(\Request('from'));
        $to = date(\Request('to'));

        $reservation = FilledReservation::query();
        $reservation->where('restaurant_id', auth()->user()->restaurant_id)
            ->select('*')
            ->selectRaw("str_to_date(time,'%h:%i %p') as f_time");

        if (!empty($search))
            $reservation->where('name', 'LIKE', "%$search%");

        if (\Request('from') && \Request('to'))
            $reservation->whereBetween('date', [$from, $to]);

        if (\Request('from') && !\Request('to')) {
            $reservation->whereDate('date', $from);
        }

        if (!\Request('from') && !\Request('to')) {
            $reservation->whereDate('date', Carbon::today());
        }

        $reservation->where('type', '1')
            ->where('is_walkin', '1')
            ->orderBy('created_at', 'DESC');

        $data['count_pax'] = $reservation->sum('no_of_occupancy');
        $data['reservation'] = $reservation
            ->orderBy('f_time', 'ASC')->paginate(6);
           
            // Your Eloquent query executed by using get()
            
            // 
        return $data;
    }

    public function all()
    {
        $reservation = FilledReservation::query();
        $reservation->where('restaurant_id', auth()->user()->restaurant_id)
            ->select('*')
            ->selectRaw("str_to_date(time,'%h:%i %p') as f_time");

        $search = \Request('filter');
        $from = date(\Request('from'));
        $to = date(\Request('to'));

        $data = collect();

        if (!empty($search))
            $reservation->where('name', 'LIKE', "%$search%");

        if (\Request('from') && \Request('to'))
            $reservation->whereBetween('date', [$from, $to]);

        if (!\Request('from') && !\Request('to')) {
            $reservation->whereDate('date', Carbon::today());
        }

        if (\Request('from') && !\Request('to')) {
            $reservation->whereDate('date', \Request('from'));
        }

        $reservation->where('is_walkin', null);
        $reservation->whereNotIn('status', ['0', '2']);
        $reservation->orderBy('f_time', 'ASC');

        $data['count_pax'] = $reservation->sum('no_of_occupancy');
        $data['reservation'] = $reservation->paginate(6);
        return $data;
    }

    public function deleted()
    {
        $reservation = FilledReservation::query();
        $reservation->where('restaurant_id', auth()->user()->restaurant_id)
            ->select('*')
            ->selectRaw("str_to_date(time,'%h:%i %p') as f_time");

        $search = \Request('filter');
        $from = date(\Request('from'));
        $to = date(\Request('to'));

        $data = collect();

        if (!empty($search))
            $reservation->where('name', 'LIKE', "%$search%");

        if (\Request('from') && \Request('to'))
            $reservation->whereBetween('date', [$from, $to]);

        if (!\Request('from') && !\Request('to')) {
            $reservation->whereDate('date', Carbon::today());
        }

        if (\Request('from') && !\Request('to')) {
            $reservation->whereDate('date', \Request('from'));
        }

        $reservation
            ->withTrashed()
            ->where('deleted_at', '!=', null);
        $reservation->orderBy('f_time', 'ASC');

        $data['count_pax'] = $reservation->sum('no_of_occupancy');
        $data['reservation'] = $reservation->paginate(6);

        return $data;
    }

    public function left()
    {
        $reservation = FilledReservation::query();
        $reservation->where('restaurant_id', auth()->user()->restaurant_id)
            ->select('*')
            ->selectRaw("str_to_date(time,'%h:%i %p') as f_time");

        $search = \Request('filter');
        $from = date(\Request('from'));
        $to = date(\Request('to'));

        if (!empty($search))
            $reservation->where('name', 'LIKE', "%$search%");

        if (\Request('from') && \Request('to'))
            $reservation->whereBetween('date', [$from, $to]);

        if (!\Request('from') && !\Request('to')) {
            $reservation->whereDate('date', Carbon::today());
        }

        if (\Request('from') && !\Request('to')) {
            $reservation->whereDate('date', \Request('from'));
        }

        $reservation->orderBy('f_time', 'ASC');

        $reservation
            ->where('status', 5);

        $data['count_pax'] = $reservation->sum('no_of_occupancy');
        $data['reservation'] = $reservation->paginate(6);

        return $data;
    }

    public function default()
    {
        $reservation = FilledReservation::query();

        $reservation->where('restaurant_id', auth()->user()->restaurant_id)
            ->select('*')
            ->selectRaw("str_to_date(time,'%h:%i %p') as f_time");

        $from = date(\Request('from'));
        $to = date(\Request('to'));
        $search = \Request('filter');

        if (!empty($search))
            $reservation->where('name', 'LIKE', "%$search%");

        if (\Request('from') && \Request('to'))
            $reservation->whereBetween('date', [$from, $to]);

        if (!\Request('from') && !\Request('to')) {
            $reservation->whereDate('date', Carbon::today());
        }

        if (\Request('from') && !\Request('to')) {
            $reservation->whereDate('date', \Request('from'));
        }

        $reservation
            ->withTrashed()
            ->where(function ($row) {
                $row->whereNotIn('status', ['0', '2', '1']);
                $row->orWhere('deleted_at', '!=', null);
            });

        $reservation->orderBy('f_time', 'ASC');

        $data['count_pax'] = $reservation->sum('no_of_occupancy');
        $data['reservation'] = $reservation->paginate(6);

        return $data;
    }


    public function customer_reservation(Request $request)
    {
        # code...
        $name = $request->input('name');
        $phone = $request->input('phone');
        $date = $request->input('date');
        $time = $request->input('time');
        $no_of_occupancy = $request->input('no_of_occupancy');
        $restaurant_id = $request->input('restaurant_id');
        $status = 0;

        DB::insert('insert into filled_reservations (name,phone,date,time,no_of_occupancy,restaurant_id,status) values(?,?,?,?,?,?,?)', [$name, $phone, $date, $time, $no_of_occupancy, $restaurant_id, $status]);
        return \Response::success(true, "Thank You! Reservation sent for Confirmation.");
    }

    public function getvendor_reservation(Request $request)
    {

        $restaurant_id = auth()->user()->restaurant_id;
        
        $reservation = FilledReservation::where('restaurant_id',$restaurant_id);
                if($request->date)
                {
                   $reservation->where("date", "=", $request->date);
                }

              return  $reservation->paginate(6);

    }

    

    public function sms($smsCrendential, $reservation_status, $request)
    {


        foreach ($smsCrendential as $value) {
            # code...
            // convert $smsCrendential objects into array
            //   fetching colunms  
            // eg $value->sender
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.msg91.com/api/v5/flow/",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{\n  \"flow_id\": \"$value->flow_id\",\n 
                                 \"sender\": \"$value->sender\",\n 
                                  \"mobiles\": \"91$request->phone\",\n 
                                  \"name\": \"$request->name\",\n 
                                  \"time\": \"$request->time\",\n 
                                  \"people\": \"$request->no_of_occupancy\",\n 
                                   \"status\": \"$reservation_status\"\n
                                }",
            CURLOPT_HTTPHEADER => [
                "authkey: 330361ANHHeBzzau62341006P1",
                "content-type: application/JSON"
            ],
        ]);

        $response = curl_exec($curl);
        // $err = curl_error($curl);

        curl_close($curl);

        // if ($err) {
        // echo "cURL Error #:" . $err;
        // } else {
        // echo $response;
        // }


    }


    public function reservation_is_walking(Request $request){
   
        $reservation_is_walking = FilledReservation::where('is_walkin', '=', 1)
         ->where("restaurant_id","=",auth()->user()->restaurant_id);
        //  return $request->date;
         if($request->date != "")
         {
            $reservation_is_walking->where("date", "=", $request->date);
         }
         return $reservation_is_walking->paginate(6);

        }

    public function reservation_is(Request $request){
        $reservation_is_walking = FilledReservation::whereRaw('(is_walkin = 0 or is_walkin is null)')
         ->where( [["restaurant_id","=",auth()->user()->restaurant_id]]);
         if($request->date!="")
         {
            $reservation_is_walking->where("date", "=", $request->date);
         }
            return $reservation_is_walking ->paginate(6);
    }
}
