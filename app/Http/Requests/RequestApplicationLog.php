<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequestApplicationLog extends FormRequest
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
            'data.id' => 'required',
            'data.attributes.name' => 'required|string',
            'data.attributes.application_type' => 'required|string'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'data.id.required' => 'ID field is required',
            'data.attributes.name.required' => 'name field is required',
            'data.attributes.name.string' => 'name field is not valid string',
            'data.attributes.application_type.required' => 'application_type field is required',
            'data.attributes.application_type.string' => 'application_type field is not valid string',
        ];
    }
}
