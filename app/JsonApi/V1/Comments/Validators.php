<?php

namespace App\JsonApi\V1\Comments;

use CloudCreativity\LaravelJsonApi\Validation\AbstractValidators;

class Validators extends AbstractValidators
{

    /*
     * Messagi di errore per i campi
     */
    protected $messages = [
       // 'author_id.required' => 'author_id ' . VALIDATOR_REQUIRED,
       // 'author_id.numeric' => 'author_id ' . VALIDATOR_NUMERIC,
        'task_id.required' => 'task_id ' . VALIDATOR_REQUIRED,
        'task_id.numeric' => 'task_id ' . VALIDATOR_NUMERIC,
        'task_id.exists' => 'task_id ' . VALIDATOR_EXIST,
        'body.required' => 'body ' . VALIDATOR_REQUIRED,
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
            'body' => 'required|string|min:1|max:255',
            'task_id' => 'required|numeric|exists:tasks,id',
          //  'author_id' => 'required|numeric',
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
