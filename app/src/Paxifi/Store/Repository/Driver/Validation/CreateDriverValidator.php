<?php namespace Paxifi\Store\Repository\Driver\Validation;

use Paxifi\Support\Validation\Validator;

class CreateDriverValidator extends Validator
{
    protected $rules = [
        'name' => 'required',
        'email' => 'required|email|unique:drivers',
        'password' => 'required|min:6|alpha_dash',
        'photo' => 'url',
        'currency' => 'required',
        'tax_enabled' => 'boolean',
        'tax_included_in_price' => 'boolean',
        'tax_global_amount' => 'numeric|between:0,1',
        'notify_sale' => 'boolean',
        'notify_inventory' => 'boolean',
        'notify_feedback' => 'boolean',
        'notify_billing' => 'boolean',
        'notify_others' => 'boolean',
    ];
} 