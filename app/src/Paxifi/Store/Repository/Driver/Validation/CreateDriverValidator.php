<?php namespace Paxifi\Store\Repository\Driver\Validation;

use Paxifi\Support\Validation\Validator;

class CreateDriverValidator extends Validator
{
    protected $rules = [
        'name' => 'required',
        'seller_id' => 'required|unique:drivers|alpha_dash|max:12',
        'email' => 'required|email|unique:drivers',
        'password' => 'required',
        'photo' => 'url',
        'address' => 'required',
        'currency' => 'required',
    ];
} 