<?php

namespace App\JsonApi\V1\Boats;

use CloudCreativity\LaravelJsonApi\Validation\AbstractValidators;

class Validators extends AbstractValidators
{

    /* The messages variable. 
     * @var string[]|null
     */
    protected $messages = [
        'name.required' => 'name '.VALIDATOR_REQUIRED,
        'name.string' => 'name '.VALIDATOR_STRING,
        'registration_number.required' => 'registration_number '.VALIDATOR_REQUIRED,
        'registration_number.numeric' => 'registration_number '.VALIDATOR_NUMERIC,
        'manufacture_year.number' => 'manufacture_year '.VALIDATOR_NUMERIC,
        'manufacture_year.required' => 'manufacture_year '.VALIDATOR_REQUIRED,
        'length.number' => 'length '.VALIDATOR_NUMERIC,
        'length.required' => 'length '.VALIDATOR_REQUIRED,
        'draft.number' => 'draft '.VALIDATOR_NUMERIC,
        'draft.required' => 'draft '.VALIDATOR_REQUIRED,
        'beam.number' => 'beam '.VALIDATOR_NUMERIC,
        'beam.required' => 'draft '.VALIDATOR_REQUIRED,
    ];

    /**
     * The include paths a client is allowed to request.
     *
     * @var string[]|null
     *      the allowed paths, an empty array for none allowed, or null to allow all paths.
     */
    protected $allowedIncludePaths = [];

    /**
     * The sort field names a client is allowed send.
     *
     * @var string[]|null
     *      the allowed fields, an empty array for none allowed, or null to allow all fields.
     */
    protected $allowedSortParameters = [];

    /**
     * Get resource validation rules.
     *
     * @param mixed|null $record
     *      the record being updated, or null if creating a resource.
     * @return mixed
     */
    protected function rules($record = null): array
    {
        return [
           'name' => 'required|string|min:1|max:255',
           'registration_number' => 'required|numeric',
           'manufacture_year' => 'required|numeric',
           'length' => 'required|numeric',
           'draft' => 'required|numeric',
           'beam' => 'required|numeric',
        ];
    }
  
    /**
     * Get query parameter validation rules.
     *
     * @return array
     */
    protected function queryRules(): array
    {
        return [
            'filter.name' => 'filled|string',
            'filter.site_id' => 'integer',
        ];
    }
    

}
