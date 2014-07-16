<?php namespace Paxifi\Store\Repository\Driver\Validation;

use Paxifi\Support\Validation\Validator;

class UpdateDriverValidator extends Validator
{
    protected $rules = [
        'name' => 'sometimes|required',
        'seller_id' => 'unique:drivers|alpha_dash|max:12|sometimes|required',
        'password' => 'sometimes|required',
        'photo' => 'sometimes|url',
        'address' => 'sometimes|required',
        'currency' => 'sometimes|required',
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