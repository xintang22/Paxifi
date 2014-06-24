<?php namespace Paxifi\Store\Repository\Driver\Validation;

use Paxifi\Support\Validation\Validator;

class SettingsValidator extends Validator
{
    protected $messages = array(
        'boolean' => 'The :attribute has to be boolean.',
    );

    protected $rules = [
        'notify_sale' => 'sometimes|required|boolean',
        'notify_inventory' => 'sometimes|required|boolean',
        'notify_feedback' => 'sometimes|required|boolean',
        'notify_billing' => 'sometimes|required|boolean',
        'notify_others' => 'sometimes|required|boolean',
    ];
} 