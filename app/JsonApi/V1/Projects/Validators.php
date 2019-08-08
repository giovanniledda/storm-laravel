<?php

namespace App\JsonApi\V1\Projects;

use CloudCreativity\LaravelJsonApi\Validation\AbstractValidators;

class Validators extends AbstractValidators
{
     /* The messages variable.
     *
     * @var string[]|null
     */
    protected $messages = [
        'name.required' => 'name '.VALIDATOR_REQUIRED,
        'name.string' => 'name '.VALIDATOR_STRING,
        'boat_id.required' => 'boat_id '.VALIDATOR_REQUIRED,
        'boat_id.numeric' => 'boat_id '.VALIDATOR_NUMERIC,
        'project_type.in' => 'status '.VALIDATOR_IN.': '.PROJECT_TYPE_NEWBUILD.','.PROJECT_TYPE_REFIT,
        'status.in' => 'status '.VALIDATOR_IN.': '.PROJECT_STATUS_OPERATIONAL.','.PROJECT_STATUS_IN_SITE.','.PROJECT_STATUS_CLOSED
    ];

    /**
     * 
     *    
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
           'boat_id' => 'required|numeric',
           'project_type' => 'in:'.PROJECT_TYPE_NEWBUILD.','.PROJECT_TYPE_REFIT,
           'status' => 'in:'.PROJECT_STATUS_IN_SITE.','.PROJECT_STATUS_OPERATIONAL.','.PROJECT_STATUS_CLOSED
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
            //
        ];
    }

}
