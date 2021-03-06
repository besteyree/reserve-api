<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilledReservationRequest extends FormRequest
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
            'name' => 'required',
            'phone' => 'required|numeric',
            'email' => 'nullable|email',
            'date' => 'required|date',
            'type' => 'nullable',
            'time' => 'required',
            'adults' => 'nullable',
            'kids' => 'nullable',
            'no_of_occupancy' => 'required',
            'table_type_id' => 'nullable',
            'status' => 'nullable',
            'is_walkin' => 'nullable',
            'source' => 'nullable',
            'reserved_by' => 'nullable',
            'comment' => 'nullable'
        ];
    }
}
