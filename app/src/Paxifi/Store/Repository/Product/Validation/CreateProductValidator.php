<?php namespace Paxifi\Store\Repository\Product\Validation;

use Paxifi\Support\Validation\Validator;

class CreateProductValidator extends Validator
{
    protected $rules = [
        'category_id' => 'required',
    ];

    protected $messages = [
        'category_id.required' => 'The product category is required',
    ];

} 