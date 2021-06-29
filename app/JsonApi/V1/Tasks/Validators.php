<?php

namespace App\JsonApi\V1\Tasks;

use function array_merge;
use CloudCreativity\LaravelJsonApi\Contracts\ContainerInterface;
use CloudCreativity\LaravelJsonApi\Factories\Factory;
use CloudCreativity\LaravelJsonApi\Validation\AbstractValidators;
use function explode;
use function implode;
use const TASK_TYPE_PRIMARY;
use const TASK_TYPE_REMARK;
use const TASKS_R_STATUSES;
use const TASKS_STATUSES;
use const VALIDATOR_EXIST;
use const VALIDATOR_IN;
use const VALIDATOR_NUMERIC;
use const VALIDATOR_REQUIRED;
use const VALIDATOR_STRING;

class Validators extends AbstractValidators
{
    public function __construct(Factory $factory, ContainerInterface $container)
    {
        parent::__construct($factory, $container);

        $this->messages = [
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
            'status.in' => 'status (:input) '.VALIDATOR_IN.' '.self::getTaskStatusOptions(),
            'x_coord.required'=> 'x_coord '.VALIDATOR_REQUIRED,
            'x_coord.numeric'=> 'x_coord '.VALIDATOR_NUMERIC,
            'y_coord.required'=> 'y_coord '.VALIDATOR_REQUIRED,
            'y_coord.numeric'=> 'y_coord '.VALIDATOR_NUMERIC,
            'intervent_type_id.numeric' => 'intervent_type_id '.VALIDATOR_NUMERIC,
            'intervent_type_id.exists' => 'intervent_type_id '.VALIDATOR_EXIST,
            'zone_id.numeric' => 'zone_id '.VALIDATOR_NUMERIC,
            'zone_id.exists' => 'Zone with ID of :input '.VALIDATOR_EXIST,
            'task_type.string' => 'task_type '.VALIDATOR_STRING,
            'task_type.in' => 'task_type admitted values are: '.TASK_TYPE_REMARK.','.TASK_TYPE_PRIMARY,
            'opener_application_log_id.numeric' => 'opener_application_log_id '.VALIDATOR_NUMERIC,
            'opener_application_log_id.exists' => 'Application Log with ID of :input '.VALIDATOR_EXIST,
        ];
    }

    protected $messages; // initialized in the constructor

    /**
     * @return string
     */
    private static function getTaskStatusOptions()
    {
        return implode(',', array_merge(TASKS_STATUSES, TASKS_R_STATUSES));
    }

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
    protected $allowedSortParameters = null; // ['title', 'description']; // tutti i campi abilitati all'ordinamento

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
           'status' => 'in: '.self::getTaskStatusOptions(),
           'project_id' => 'required|numeric|exists:projects,id',
           'section_id' => 'required|numeric|exists:sections,id',
           'x_coord' => 'required|numeric',
           'y_coord' => 'required|numeric',
           'zone_id' => 'nullable|numeric|exists:zones,id',
           'task_type' => 'string|in:'.TASK_TYPE_REMARK.','.TASK_TYPE_PRIMARY,
           'intervent_type_id' => 'nullable|required_if:task_type,'.TASK_TYPE_PRIMARY.'|numeric|exists:task_intervent_types,id',
           'opener_application_log_id' => 'nullable|numeric|exists:application_logs,id',
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
            'filter.prog_nums' =>  'regex:/^(\d+(-\d+)?,?\s?)*$/',  // non c'è modo di avere un messaggio personalizzato purtroppo
            'filter.task_type' =>  'string|in:'.TASK_TYPE_REMARK.','.TASK_TYPE_PRIMARY,
            'filter.zone_id'=> 'numeric',
            'filter.opener_application_log_id'=> 'numeric',
        ];
    }
}
