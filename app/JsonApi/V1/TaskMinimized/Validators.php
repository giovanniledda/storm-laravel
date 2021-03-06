<?php

namespace App\JsonApi\V1\TaskMinimized;

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
      //  'title.required' => 'title '.VALIDATOR_REQUIRED,
        'title.string' => 'title '.VALIDATOR_STRING,
      // 'number.required' => 'number '.VALIDATOR_REQUIRED,
        'number.numeric' => 'number '.VALIDATOR_NUMERIC,
        'worked_hours.numeric' => 'worked_hours '.VALIDATOR_NUMERIC,
//        'description.string' => 'description '.VALIDATOR_STRING,
        'project_id.numeric'=> 'project_id '.VALIDATOR_NUMERIC,
        'project_id.required'=> 'project_id '.VALIDATOR_REQUIRED,
        'project_id.exists'=> 'project_id '.VALIDATOR_EXIST,
        'section_id.numeric'=> 'section_id '.VALIDATOR_NUMERIC,
        'section_id.required'=> 'section_id '.VALIDATOR_REQUIRED,
        'section_id.exists'=> 'section_id '.VALIDATOR_EXIST,
        'status.in' => 'status '.VALIDATOR_IN.' '.TASKS_STATUS_DRAFT.', '.TASKS_STATUS_SUBMITTED.', '.TASKS_STATUS_ACCEPTED.', '.TASKS_STATUS_IN_PROGRESS.', '.TASKS_STATUS_DENIED.', '.TASKS_STATUS_MONITORED.', '.TASKS_STATUS_COMPLETED,
        'x_coord.required'=> 'x_coord '.VALIDATOR_REQUIRED,
        'x_coord.numeric'=> 'x_coord '.VALIDATOR_NUMERIC,
        'y_coord.required'=> 'y_coord '.VALIDATOR_REQUIRED,
        'y_coord.numeric'=> 'y_coord '.VALIDATOR_NUMERIC,
        'intervent_type_id.required' => 'intervent_type_id '.VALIDATOR_REQUIRED,
        'intervent_type_id.numeric' => 'intervent_type_id '.VALIDATOR_NUMERIC,
        'intervent_type_id.exists' => 'intervent_type_id '.VALIDATOR_EXIST,
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
        //'title' => 'string|min:1|max:255',
//        'description' => 'string',
        'number' => 'numeric',
        'worked_hours' => 'numeric',
        'estimated_hours' => 'numeric',
        'status' => 'in:'.TASKS_STATUS_DRAFT.','.TASKS_STATUS_SUBMITTED.','.TASKS_STATUS_ACCEPTED.','.TASKS_STATUS_IN_PROGRESS.','.TASKS_STATUS_DENIED.','.TASKS_STATUS_MONITORED.','.TASKS_STATUS_COMPLETED,
        'project_id'=> 'required|numeric|exists:projects,id',
        'section_id'=> 'required|numeric|exists:sections,id',
        'intervent_type_id'=>'required|numeric|exists:task_intervent_types,id',
        'x_coord'=>'required|numeric',
        'y_coord'=>'required|numeric',
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
