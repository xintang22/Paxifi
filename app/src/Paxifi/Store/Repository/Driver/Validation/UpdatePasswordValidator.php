<?php namespace Paxifi\Store\Repository\Driver\Validation;

use Paxifi\Support\Validation\Validator;

class UpdatePasswordValidator extends Validator {
    protected $rules = [
      "origin_password" => "required|min:6|alpha_dash",
      "password" => "required|min:6|alpha_dash|confirmed"
    ];
} 