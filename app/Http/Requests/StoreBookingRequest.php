<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'date_from' => [
                'required',
                'date_format:d-m-Y',
            ],
            'date_to' => [
                'required',
                'date_format:d-m-Y',
            ],
            'car_registration' => [
                'required',
                'regex:/[A-Z]{2}[0-9]{2} ?[A-Z]{3}/'
            ],
            'customer_name' => [
                'required',
                'string'
            ]
        ];
    }

    public function messages()
    {
        return [
            'date_from.required' => 'date_from field is required', //prevent laravel removing _ in message
            'date_to.required' => 'date_to field is required', //prevent laravel removing _ in message
            'date_from.date_format' => "date_from format must be in dd-mm-yyyy format",
            'date_to.date_format' => "date_to format must be in dd-mm-yyyy format",
            'number_plate.regex' => 'number_plate must be in format AA11 AAA'
        ];
    }
}
