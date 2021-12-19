<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index()
    {
        $user = User::query();
        $user->with('restaurant.floor:id,title,restaurant_id', 'restaurant.floor.table:id,title,no_of_occupany,floor_id,type_id,status,user_id', 'restaurant.floor.table.tableType:id,title');

        if(auth()->user()->user_type == 1){
            if(\request('user_type'))
                $user->where('user_type', \request('user_type'));

            return $user->paginate(6);
        }

        return $user->find(auth()->id());
    }

    public function register(UserRequest $request)
    {
        $input = $request->except('password', 'confirm_password');
        $input['uuid'] = (string) \Str::uuid()->getHex();
        $input['password'] = Hash::make($request->password);

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

    public function login(Request $request)
    {
        $user = User::with('restaurant.floor:id,title,restaurant_id', 'restaurant.floor.table:id,title,no_of_occupany,floor_id,type_id,status,user_id', 'restaurant.floor.table.tableType:id,title')
            ->where('email', $request->email)->first();

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

        return response()->success([
            'type' => 'Bearer',
            'token' => $user->createToken('journalist-token')->plainTextToken,
            'user' => $user
        ], "Success");
    }

    public function logout()
    {
        $user = auth()->user();
        $user->currentAccessToken()->delete();

        return response()->success(null, "Logged out!");
    }
}
