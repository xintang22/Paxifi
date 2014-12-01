<?php
/**
 * Created by PhpStorm.
 * User: Sonny
 * Date: 12/1/14
 * Time: 5:58 PM
 */

namespace Paxifi\Store\Repository\Driver\Validation;


use Paxifi\Support\Validation\Validator;

class EmailValidation extends Validator {
    protected $rules = [
        'email' => 'required|email|unique:drivers',
    ];
} 