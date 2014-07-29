<?php namespace Paxifi\Payment\Repository\Validation;

use Paxifi\Support\Validation\Validator;

class UpdatePaymentValidator extends Validator {
    protected $rules = [
        "status" => 'in: -1, 1|integer',
    ];
} 