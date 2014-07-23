<?php namespace Paxifi\Store\Repository\Driver\Validation;

use Paxifi\Support\Validation\Validator;

class UpdateDriverSellerIdValidator extends Validator
{
    protected $rules = [
        'seller_id' => 'unique:drivers|alpha_dash|max:12|required',
    ];
}