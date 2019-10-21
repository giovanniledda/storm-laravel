<?php

namespace App\JsonApi\V1\Users;

use CloudCreativity\LaravelJsonApi\Validation\AbstractValidators;
use const VALIDATOR_EMAIL_UNIQUE;

class Validators extends AbstractValidators
{

     /* The messages variable. 
     * @var string[]|null
     */
    protected $messages = [
        'name.required' => 'name '.VALIDATOR_REQUIRED,
        'name.string' => 'name '.VALIDATOR_STRING,
        'surname.required' => 'surname '.VALIDATOR_REQUIRED,
        'surname.string' => 'surname '.VALIDATOR_STRING,
        'password.required' => 'password '.VALIDATOR_REQUIRED,
        'password.string' => 'password '.VALIDATOR_STRING,
        'email.unique' => VALIDATOR_EMAIL_UNIQUE,
  //      'email.required' => 'email '.VALIDATOR_REQUIRED,
  //      'email.email' => 'email '.VALIDATOR_EMAIL,
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
           'surname' => 'required|string|min:1|max:255',   
           'email' => 'required|email:rfc,dns|unique:users',
           'password' => 'required|string|min:1|max:255',   
           'is_storm' => 'required|numeric'
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
