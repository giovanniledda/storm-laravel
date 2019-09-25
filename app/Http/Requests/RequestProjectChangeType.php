<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use const PROJECT_TYPE_NEWBUILD;
use const PROJECT_TYPE_REFIT;

class RequestProjectChangeType extends FormRequest
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
            'data.attributes.type' => 'required|in:'.PROJECT_TYPE_NEWBUILD.','.PROJECT_TYPE_REFIT,
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
            'data.attributes.type' => 'type attribute admitted values:'.PROJECT_TYPE_NEWBUILD.','.PROJECT_TYPE_REFIT,
        ];
    }
}
