<?php

namespace App\Http\Controllers\Api\Restaurant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sms_Message;
use DB;

class SmsMessageController extends Controller
{
    //

    public function index($id=null)
    {     
        if(auth()->user()->user_type == 1){
       
        if($id == null){
           return Sms_Message::get();
        }

         return Sms_Message::where('restaurant_id','=',$id)->get();
           
         }

    }
   
    public function store(Request $request, $id=null)
    {
        if(auth()->user()->user_type == 1){
           
            if($id){                   
                try {                  
                    $sms_message = new Sms_Message;
                    $sms_message->flow_id = $request->input('flow_id');
                    $sms_message->sender = $request->input('sender');
                    $sms_message->restaurant_id = $id;
                    $sms_message->save();
                    return response()->json(['message' => 'SMS Created Successfully ']);

                    } catch (Exception $e) {
                        return response()->json(['message' => 'Failed']);

                }       
            }
        }
    }

    public function update(Request $request, $id=null)
    {
        # code...
        if(auth()->user()->user_type == 1){
            if($id){    
                try {             
                    $sms_message = Sms_Message::find($id);
                    $sms_message->flow_id = $request->input('flow_id');
                    $sms_message->sender = $request->input('sender');
                    $sms_message->update();
                    return response()->json(['message' => 'SMS Updated Successfully ']);
                    
                } catch (Exception $e) {
                    return response()->json(['message' => 'Failed']);
                }                                 
            }
        }      
    }

    public function destroy($id)
    {
        if(auth()->user()->user_type == 1){
                       
            if($id){               
                $sms_message = Sms_Message::find($id);
                $sms_message->delete();
                return response()->json(['message' => 'Deleted']);
            }
        }
    }


  


}
