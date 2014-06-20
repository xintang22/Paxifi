<?php namespace Paxifi\Support\Validation;

use Illuminate\Validation\Factory;

abstract class Validator
{
    /**
     * @var \Illuminate\Validation\Factory
     */
    protected $validator;

    /**
     * @var \Illuminate\Validation\Validator
     */
    protected $validation;

    /**
     * @var array
     */
    protected $rules = [];

    /**
     * @var array
     */
    protected $messages = [];


    /**
     * @param Factory $validator
     */
    function __construct(Factory $validator = null)
    {
        $this->validator = $validator ? : \App::make('validator');
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
        $this->validation = $this->validator->make(
            $data,
            $this->getValidationRules(),
            $this->getValidationMessages()
        );

        if ($this->validation->fails()) {
            throw new ValidationException('Validation failed', $this->getValidationErrors());
        }

        return true;
    }

    /**
     * @return array
     */
    public function getValidationRules()
    {
        return $this->rules;
    }

    /**
     * Set the validation rules.
     *
     * @param $rules
     *
     * @return $this
     */
    public function setValidationRules($rules)
    {
        $this->rules = $rules;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValidationErrors()
    {
        return $this->validation->errors();
    }

    /**
     * @return mixed
     */
    public function getValidationMessages()
    {
        return $this->messages;
    }

    /**
     * Register custom validation rules
     *
     * @return void
     */
    protected function registerExtensions()
    {
        $this->validator->extend('address', function ($attribute, $value, $parameters) {
            return $this->validator->make($value, [
                'street' => 'required',
                'city' => 'required',
                'country' => 'required',
                'postcode' => 'required',
            ])->passes();
        });
    }

}