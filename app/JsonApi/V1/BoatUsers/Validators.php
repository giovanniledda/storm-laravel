<?php

namespace App\JsonApi\V1\BoatUsers;

use CloudCreativity\LaravelJsonApi\Validation\AbstractValidators;

class Validators extends AbstractValidators
{
    protected $messages = [
        'boat_id.required' => 'boat_id '.VALIDATOR_REQUIRED,
        'boat_id.numeric' => 'boat_id '.VALIDATOR_NUMERIC,
        'boat_id.exists' => 'boat_id '.VALIDATOR_EXIST,

        'profession_id.required' => 'profession_id '.VALIDATOR_REQUIRED,
        'profession_id.numeric' => 'profession_id '.VALIDATOR_NUMERIC,
        'profession_id.exists' => 'profession_id '.VALIDATOR_EXIST,

        'user_id.required' => 'user_id '.VALIDATOR_REQUIRED,
        'user_id.numeric' => 'user_id '.VALIDATOR_NUMERIC,
        'user_id.exists' => 'user_id '.VALIDATOR_EXIST,
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
    protected function rules($record = null, array $data): array
    {
        return [
            'boat_id'=> 'required|numeric|exists:boats,id',
            'profession_id'=> 'required|numeric|exists:professions,id',
            'user_id'=> 'required|numeric|exists:users,id',
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
