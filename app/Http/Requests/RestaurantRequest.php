<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RestaurantRequest extends FormRequest
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
        return [
            'title' => 'required',
            'phone' => 'required|number',
            'additional_phone' => 'nullable|number',
            'opening_time' => 'required|date',
            'closing_time' => 'required|date',
            'max_table_occupancy' => 'numeric|required',
            'detail' => 'nullable',
            'status' => 'nullable|integer',
            'city' => 'nullable',
            'state' => 'nullable',
            'lat' => 'nullable',
            'long' => 'nullable',
            'country' => 'nullable',
        ];
    }
}
