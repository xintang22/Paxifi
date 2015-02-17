<?php namespace Paxifi\Shipment\Repository\Validation;

use Paxifi\Support\Validation\Validator;

class CreateShipmentValidator extends Validator
{
    protected $rules = [
        "sticker_id" => "required",
        "address.street" => "required",
        "address.city" => "required",
        "address.country" => "required",
        "address.postcode" => "required",
        "status" => "in:shipped,waiting,printed",
        "paypal_payment_status" => "in:pending,completed",
        "paypal_payment_id" => "required|unique:shipments"
    ];

    protected $messages = [
        'address.street.required' => 'The street field is required.',
        'address.city.required' => 'The city field is required.',
        'address.country.required' => 'The country field is required.',
        'address.postcode.required' => 'The postcode field is required.',
        'paypal_payment_id' => [
            'unique' => 'The shipment already exists.'
        ],
    ];
} 