<?php namespace Paxifi\Store\Repository\Driver\Validation;

use Paxifi\Support\Validation\Validator;

class UpdateDriverValidator extends Validator
{
    protected $rules = [
        'name' => 'sometimes|required',
        'password' => 'sometimes|required',
        'photo' => 'sometimes|url',
        'address' => 'sometimes|required',
        'currency' => 'sometimes|required',
    ];

}