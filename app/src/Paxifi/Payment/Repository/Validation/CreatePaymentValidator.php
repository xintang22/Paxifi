<?php namespace Paxifi\Payment\Repository\Validation;

use Paxifi\Support\Validation\Validator;

class CreatePaymentValidator extends Validator
{
    /**
     * @var array
     */
    protected $rules = [
        'order_id' => 'unique:payments'
    ];

    /**
     * @var array
     */
    protected $messages = [
        'unique' => 'The payment has already exists.'
    ];
}