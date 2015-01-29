<?php namespace Paxifi\Store\Repository\Driver\Validation;

use Illuminate\Validation\Factory;
use Paxifi\Paypal\Paypal;
use Paxifi\Support\Validation\Validator;
use Paxifi\Support\Validation\ValidationException;

class RegisterDriverValidator extends Validator
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

    /**
     * @var Paypal
     */
    private $paypal;

    /**
     * @param Factory $validator
     */
    function __construct(Factory $validator, Paypal $paypal)
    {
        parent::__construct($validator);

        $this->paypal = $paypal;
    }

    /**
     * Validate the form data
     *
     * @param array $data
     *
     * @return mixed
     * @throws ValidationException
     */
    public function validate(array $data)
    {
        parent::validate($data);

        // $this->paypal->verifyAuthorizationCode($data['paypal_code'], true);
    }

}