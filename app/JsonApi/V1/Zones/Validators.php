<?php

namespace App\JsonApi\V1\Zones;

use App\Zone;
use CloudCreativity\LaravelJsonApi\Contracts\Validation\ValidatorFactoryInterface;
use CloudCreativity\LaravelJsonApi\Validation\AbstractValidators;
use CloudCreativity\LaravelJsonApi\Contracts\Validation\ValidatorInterface;
use const VALIDATOR_EXIST;
use const VALIDATOR_NUMERIC;
use const VALIDATOR_REQUIRED;
use const VALIDATOR_STRING;

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
    protected $allowedFilteringParameters = ['project_id', 'level'];

    /**
     * The messages variable.
     * @var string[]|null
     */
    protected $messages = [
        'code.required' => 'code '.VALIDATOR_REQUIRED,
        'code.string' => 'code '.VALIDATOR_STRING,
        'description.string' => 'description '.VALIDATOR_STRING,
        'extension.required' => 'extension '.VALIDATOR_REQUIRED,
        'extension.numeric' => 'extension '.VALIDATOR_NUMERIC,
        'project_id.required' => 'project_id '.VALIDATOR_REQUIRED,
        'project_id.numeric' => 'project_id '.VALIDATOR_NUMERIC,
        'project_id.exists'=> 'The Project with ID :input '.VALIDATOR_EXIST,
        'description.unique'=> 'The description :input has already been taken!',
    ];

    /**
     * @param array $document
     * @return ValidatorInterface
     */
    public function create(array $document): ValidatorInterface
    {
        $validator = parent::create($document);

        // Questa regola si legge così:
        // - la descrizione dev'essere univoca tra zone padri all'interno dello stesso progetto
        $validator->sometimes('description', 'unique:zones,description', function ($input) {
            $data = [
                'description' => $input->description,
                'project_id' => $input->project_id,
            ];
            return !isset($input->parent_zone_id) && Zone::countWithAttributesAndValues($data);
        });

        // Questa regola si legge così:
        // - la descrizione dev'essere univoca tra zone figlie sotto la stessa zona padre
        $validator->sometimes('description', 'unique:zones,description', function ($input) {
            $data = [
                'description' => $input->description,
                'parent_zone_id' => $input->parent_zone_id,
            ];
            return isset($input->parent_zone_id) && Zone::countWithAttributesAndValues($data);
        });
        return $validator;
    }

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
            'code' => 'required_with:parent_zone_id|string|min:1|max:10',
            'description' => 'required|string|min:1|max:255',
            'extension' => 'required|numeric',
            'parent_zone_id' => 'numeric|exists:zones,id',
            'project_id' => 'required|numeric|exists:projects,id',
        ];
    }


    protected $queryMessages = [
        'filter.project_id.exists' => 'The Project with ID :input does not exist.',
        'filter.level.in' => 'The level filter must be one of the following types: :values',
    ];

    /**
     * Get query parameter validation rules.
     *
     * @return array
     */
    protected function queryRules(): array
    {
        return [
            'filter.project_id' => 'numeric|exists:projects,id',
            'filter.level' => 'in:f,c',
        ];
    }

}
