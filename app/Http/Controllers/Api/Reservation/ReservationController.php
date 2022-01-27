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
            $restaurant = FilledReservation::query();
            $restaurant->where('restaurant_id', $id);
            $search = \Request('filter');

            if(\Request('day') == 'history'){
                if( \Request('filter'))
                    $restaurant->where('name','LIKE' ,"%$search%");

                $restaurant->where('status', 4);
                $restaurant->orderBy('seated_date', 'DESC');
                return $restaurant->paginate(6);
            }

            if(in_array(\Request('status'), ['0', '2', '3'])){
                if( \Request('filter'))
                    $restaurant->where('name','LIKE' ,"%$search%");

                $restaurant->where('status', \Request('status'));
            }


            if(\Request('day') == 'waitlisted'){
                if( \Request('filter'))
                    $restaurant->where('name', 'LIKE' ,"%$search%");

                if(\Request('wait_to')){
                    if(\Request('wait_from') == '8'){
                        $restaurant->where('no_of_occupancy', '>', 8)
                        ->where('status', 1);
                    }else{
                        $restaurant->where('no_of_occupancy', '>', \Request('wait_from'))
                        ->where('no_of_occupancy', '<', \Request('wait_to'))
                        ->where('status', 1);
                    }

                }

                $restaurant->where('status', 1);
                return  $restaurant->paginate(6);
            }

            $restaurant->where('status', '!=', 1);

            if(\Request('day') == 'today'){
                if( \Request('filter'))
                    $restaurant->where('name','LIKE' ,"%$search%");

                $restaurant->whereDate('date', Carbon::today());

                if(!in_array(\Request('status'), ['0', '2', '3']))
                    $restaurant->whereIn('status', ['0', '2', '3']);

            }

            if(\Request('day') == 'tomorrow'){
                if( \Request('filter'))
                    $restaurant->where('name','LIKE' ,"%$search%");

                $restaurant->whereDate('date', Carbon::tomorrow())
                ->where('status', '!=', 5)
                ->where('status', '!=', 4);
            }

            if(!in_array(\Request('day'), ['today', 'tomorrow'])){

                if(\Request('status') == 'is_day')
                {
                    if( \Request('filter'))
                        $restaurant->where('name','LIKE' ,"%$search%");

                    $restaurant->where('status', '!=', 5);
                    $restaurant->whereDay('date', date('d', strtotime(\Request('day'))))
                    ->whereMonth('date', date('m', strtotime(\Request('day'))))
                    ->whereYear('date', date('Y', strtotime(\Request('day'))));

                    return $restaurant->get();
                }

                if(\Request('status') == 'is_history')
                {
                    if( \Request('filter'))
                        $restaurant->where('name','LIKE' ,"%$search%");

                    $restaurant->where('status', 4)
                    ->whereDay('date', date('d', strtotime(\Request('day'))))
                    ->whereMonth('date', date('m', strtotime(\Request('day'))))
                    ->whereYear('date', date('Y', strtotime(\Request('day'))));

                    $restaurant->orderBy('seated_date', 'DESC');
                    return $restaurant->get();
                }

                if(\Request('status') == 'is_history_cal')
                {
                    $restaurant->where('status', 4)
                    ->whereMonth('date', date('m', strtotime(\Request('day'))) + 1)
                    ->whereYear('date', date('Y', strtotime(\Request('day'))));

                    return $restaurant->get();
                }

                $restaurant
                ->select('id', 'date', 'time', 'name', 'status')
                ->where('status', '!=', 5)
                ->where('status', '!=', 4)
                ->whereMonth('date', date('m', strtotime(\Request('day'))) + 1)
                ->whereYear('date', date('Y', strtotime(\Request('day'))));
                return $restaurant->get();
            }

            return $restaurant->paginate(6);
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

                $restaurant = FilledReservation::find($id)
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
            $restaurant = FilledReservation::create($input);

        
            $message = $input['name'].' Just asked for reservation';

            foreach(DeviceToken::get() as $data){

                $get = $data->token;
                $curl = curl_init();

                curl_setopt_array($curl, array(
                CURLOPT_URL => "https://fcm.googleapis.com/fcm/send",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "{\n    \"notification\": {\n        \"title\": \"New Reservation\",\n        \"body\": \"$message\",\n        \"click_action\": \"http://localhost:3000/\",\n        \"icon\": \"https://i.imgur.com/5zO5cce.png\"\n    },\n    \"to\": \"$get\"\n}",
                CURLOPT_HTTPHEADER => array(
                    "authorization: key=AAAAuJEaeRY:APA91bEpgUMWlt1Z_8n_JG5cfIOrkpuXKYmFDMDHSHeCXgNFwWA_Y-hxU8MmwRGNZB5ltaujEE_8k_KtrdhHXBYg7bO02--zCKciUgk1GmTdFJDG133RT_PiE5v0vuPXKH1dTVk789Jw",
                    "cache-control: no-cache",
                    "content-type: application/json",
                    "postman-token: 3765bcea-b36e-1dd1-6d4a-4d3bcfe2e4e4"
                ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);

                curl_close($curl);

                if ($err) {
                echo "cURL Error #:" . $err;
                } else {
                echo $response;
                }
            }

            return \Response::success($restaurant, "Thank You! Reservation sent for Confirmation.");

        }catch(Exception $e) {
            return \Response::failed($e, 'Reservation Updated failed');
        }
    }

    public function destroy($id)
    {
        FilledReservation::destroy($id);
        return \Response::success(null, "Reservation Successfully deleted!");
    }

    public function getUserFmPhone(Request $request)
    {
        return FilledReservation::where('phone', $request->phone)
        ->first();
    }

    public function saveToken(Request $request){

        DeviceToken::create([
            'device' => $request->device,
            'token' => $request->token,
        ]);

        return 'success';
    }
}
