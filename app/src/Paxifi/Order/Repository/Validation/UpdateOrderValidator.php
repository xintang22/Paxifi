<?php namespace Paxifi\Order\Repository\Validation;

use Paxifi\Support\Validation\Validator;

class UpdateOrderValidator extends Validator
{
    protected $rules = [
        'feedback' => 'in: -1, 0, 1|integer',
        'buyer_email' => 'email'
    ];
}