<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequestAddress extends FormRequest
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
        // Verificate secondo Address::getValidationRules()
        return [
            'street'       => 'required|string|min:3|max:60',
            'street_extra' => 'string|min:3|max:60',
            'city'         => 'required|string|min:3|max:60',
            'post_code'    => 'required|min:4|max:10|AlphaDash',
            'state'        => 'string|min:3|max:60',
            'country' => 'required|max:3', // ISO3166_2 or ISO3166_3
        ];
    }
}
