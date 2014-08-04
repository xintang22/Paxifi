<?php namespace Paxifi\Store\Repository\Product\Validation;

use Paxifi\Support\Validation\Validator;

class CreateProblemValidator extends Validator {
    protected $rules = [
        "problem_type_id" => "required|exists:problem_types,id",
        "product_id" => "required|exists:products,id",
        "payment_id" => "required|exists:payments,id"
    ];
}