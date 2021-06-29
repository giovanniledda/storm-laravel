<?php

namespace App\Http\Requests;

use const APPLICATION_LOG_SECTION_TYPE_APPLICATION;
use const APPLICATION_LOG_SECTION_TYPE_INSPECTION;
use const APPLICATION_LOG_SECTION_TYPE_PREPARATION;
use const APPLICATION_LOG_SECTION_TYPE_ZONES;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use function implode;

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
            'data.attributes.application_type' => 'required|string|'.
                Rule::in([
                    APPLICATION_TYPE_PRIMER,
                    APPLICATION_TYPE_FILLER,
                    APPLICATION_TYPE_HIGHBUILD,
                    APPLICATION_TYPE_UNDERCOAT,
                    APPLICATION_TYPE_COATING,
                ]),
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
            'data.attributes.application_type.in' => 'admitted application_type values are only: '.
            implode(', ', [
                APPLICATION_TYPE_PRIMER,
                APPLICATION_TYPE_FILLER,
                APPLICATION_TYPE_HIGHBUILD,
                APPLICATION_TYPE_UNDERCOAT,
                APPLICATION_TYPE_COATING,
            ]),
        ];
    }
}
