<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index()
    {
        $user = User::query();
        $user->with('restaurant.floor:id,title,restaurant_id', 'restaurant.floor.table:id,title,no_of_occupany,floor_id,type_id,status,user_id', 'restaurant.floor.table.tableType:id,title', 'restaurant.floor.table.user');

        if(auth()->user()->user_type == 1){
            if(\request('user_type'))
                $user->where('user_type', \request('user_type'));

            return $user->paginate(6);
        }

        return $user->find(auth()->id());
    }

    public function register(Request $request, $id = null)
    {
        $input = $request->except('password', 'confirm_password');
        $input['uuid'] = (string) \Str::uuid()->getHex();
        $input['password'] = Hash::make($request->password);
      
        if($id != "null"){
            $input['restaurant_id'] = $id;
        }
       
        try{
            $user = User::create($input);
            return response()->success([
                'type' => 'Bearer',
                'token' => $user->createToken('journalist-token')->plainTextToken,
            ], "Success");
        }catch(\Exception $e) {
            return \Response::failed($e);
        }

    }


    public function update(Request $request, $id)
    { 
            $input = $request->except('password', 'confirm_password');
            $input['uuid'] = (string) \Str::uuid()->getHex();
            $input['password'] = Hash::make($request->password);
    
            try{
                $user = User::find($id);
                $user->update($input);
                return "Vendor Updated Successfully";
            }catch(\Exception $e) {
                return \Response::failed($e);
            }   
    }

    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if (! $user) {
            throw ValidationException::withMessages([
                'email' => 'The email is not found.',
            ]);
        }

        if (! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'The password does not match.',
            ]);
        }

        // $restaurant = DB::select('select id from restaurants where user_id= "'.$user->id.'"');
        
        // if($restaurant)
        // {
        // return response()->success([

        //    'status' => 200,
        //     'type' => 'Bearer',
        //     'token' => $user->createToken('journalist-token')->plainTextToken,
        //     'user' => $user,
        //     'restaurant_id'=>$restaurant[0]->id
        //  ], "Success");
        // }
        // else{
            return response()->success([

                'status' => 200,
                 'type' => 'Bearer',
                 'token' => $user->createToken('journalist-token')->plainTextToken,
                 'user' => $user,
                
              ], "Success");
        // }
    }

    public function logout()
    {
        $user = auth()->user();
        $user->currentAccessToken()->delete();

        return response()->success(null, "Logged out!");
    }

    public function getUser($restaurant_id){
        return User::where('restaurant_id', auth()->user()->restaurant_id)->get();
    }


    //fetch vendor(admin)

    public function getvendor($id = null)
    {
        # code...
        if (auth()->user()->user_type == 1 ){
            if ($id) {
                return User::select('*', )->where([['user_type', '=', 2],['id','=', $id]])->get();
            }
            
            return User::where('user_type', '=', 2)->get();
         }
    }





    
}
