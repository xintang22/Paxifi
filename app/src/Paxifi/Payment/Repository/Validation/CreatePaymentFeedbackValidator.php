<?php namespace Paxifi\Payment\Repository\Validation;

use Paxifi\Support\Validation\Validator;

class CreatePaymentFeedbackValidator extends Validator {
    /**
     * @var array
     */
    protected $rules = [
        'payment_id' => 'unique:feedbacks|exists:payments,id',
        'driver_id' => 'exists:drivers,id',
    ];

    /**
     * @var array
     */
    protected $messages = [
        'unique' => 'The feedback to this payment has already exists.',
        'exists.payment_id' => 'The payment not exists at all.'
    ];
} 