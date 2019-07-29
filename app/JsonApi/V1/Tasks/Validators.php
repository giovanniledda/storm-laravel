<?php

namespace App\JsonApi\V1\Tasks;

use CloudCreativity\LaravelJsonApi\Validation\AbstractValidators;

class Validators extends AbstractValidators
{

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
    protected $allowedSortParameters = null;// ['title', 'description']; // tutti i campi abilitati all'ordinamento

    protected $messages = [
        'title.required' => 'title '.VALIDATOR_REQUIRED,
        'title.string' => 'title '.VALIDATOR_STRING,
        'number.required' => 'number '.VALIDATOR_REQUIRED,
        'number.numeric' => 'number '.VALIDATOR_NUMERIC,
        'worked_hours.numeric' => 'worked_hours '.VALIDATOR_NUMERIC,
        'description.string' => 'description '.VALIDATOR_STRING,
        'project_id.numeric'=> 'project_id '.VALIDATOR_NUMERIC,
        'project_id.required'=> 'project_id '.VALIDATOR_REQUIRED,
        'section_id.numeric'=> 'section_id '.VALIDATOR_NUMERIC,
        'section_id.required'=> 'section_id '.VALIDATOR_REQUIRED,
        'status.in' => 'status '.VALIDATOR_IN.': '.TASKS_STATUS_ACCEPTED.','.TASKS_STATUS_CLOSED.','.TASKS_STATUS_DENIED.','.TASKS_STATUS_SUBMITTED
    ];
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
        'title' => 'required|string|min:1|max:255',
        'description' => 'string',
        'number' => 'required|numeric',
        'worked_hours' => 'numeric',
        'estimated_hours' => 'numeric',
        'status' => 'in:'.TASKS_STATUS_ACCEPTED.','.TASKS_STATUS_CLOSED.','.TASKS_STATUS_DENIED.','.TASKS_STATUS_SUBMITTED,
        'project_id'=> 'required|numeric',
        'section_id'=> 'required|numeric',
        'intervent_type_id'=>'required|numeric',
        'subsection_id'=>'numeric',
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
