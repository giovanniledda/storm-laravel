<?php

namespace App\JsonApi\V1\Documents;

use CloudCreativity\LaravelJsonApi\Validation\AbstractValidators;

class Validators extends AbstractValidators
{
    protected $messages = [
        'type.required' => 'type '.VALIDATOR_REQUIRED,
        'type.string' => 'type '.VALIDATOR_STRING,
        'title.required' => 'title '.VALIDATOR_REQUIRED,
        'title.string' => 'title '.VALIDATOR_STRING,
        'file.required' => 'file '.VALIDATOR_REQUIRED,
        'file.string' => 'file '.VALIDATOR_STRING,
        'filename.required' => 'filename '.VALIDATOR_REQUIRED,
        'filename.string' => 'filename '.VALIDATOR_STRING,
        'entity_type.required' => 'entity_type '.VALIDATOR_REQUIRED,
        'entity_type.string' => 'entity_type '.VALIDATOR_STRING,
        'entity_id.required' => 'entity_id '.VALIDATOR_REQUIRED,
        'entity_id.numeric' => 'entity_id '.VALIDATOR_NUMERIC,
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
            'type' => 'required|string|min:1|max:255',
            'title' => 'required|string|min:1|max:255',
            'file' => 'required|string|min:1',
            'filename' => 'required|string|min:1|max:255',
            'entity_type' => 'required|in:' . implode(',', DOCUMENT_RELATED_ENTITIES),
            'entity_id'  => 'required|numeric',
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
