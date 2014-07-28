<?php namespace Paxifi\Payment\Repository\Validation;

use Paxifi\Support\Validation\Validator;

class CreatePaymentValidator extends Validator
{
    protected $rules = [
        'order_id' => 'unique:payments'
    ];
}