<?php

namespace App\JsonApi\V1\ProjectTools;

use CloudCreativity\LaravelJsonApi\Validation\AbstractValidators;
use const VALIDATOR_EXIST;
use const VALIDATOR_NUMERIC;
use const VALIDATOR_REQUIRED;

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
    protected $allowedSortParameters = [];

    /**
     * The filters a client is allowed send.
     *
     * @var string[]|null
     *      the allowed filters, an empty array for none allowed, or null to allow all.
     */
    protected $allowedFilteringParameters = [];

    /**
     * The error messages.
     *
     * @var string[]|null
     */
    protected $messages = [
        'tool_id.required' => 'tool_id '.VALIDATOR_REQUIRED,
        'tool_id.numeric' => 'tool_id '.VALIDATOR_NUMERIC,
        'tool_id.exists' => 'Tool with tool_id :input '.VALIDATOR_EXIST,

        'project_id.required' => 'project_id '.VALIDATOR_REQUIRED,
        'project_id.numeric' => 'project_id '.VALIDATOR_NUMERIC,
        'project_id.exists' => 'Project with project_id :input '.VALIDATOR_EXIST,
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
            'project_id'=> 'required|numeric|exists:projects,id',
            'tool_id'=> 'required|numeric|exists:tools,id',
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
