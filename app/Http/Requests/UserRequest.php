<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
            'religion_id' => 'required|integer',
            'type' => 'required|integer',
            'phone' => 'required|nullable',
        ];

        if(\Request::wantsJson()){
            $rules = array_merge($rules, [
                'confirm_password' => 'required|same:password',
            ]);
        }

    }
}
