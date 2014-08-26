<?php namespace Paxifi\Subscription\Repository\Validation;

use Paxifi\Support\Validation\Validator;

class CreateSubscriptionValidator extends Validator
{
    protected $rules = [
        "ipn_track_id" => "unique:subscriptions,ipn_track_id"
    ];

    protected $messages = [
        'unique' => 'The subscription is already exists.'
    ];
}