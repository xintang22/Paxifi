<?php namespace Paxifi\Support\Repository;

use Illuminate\Database\Eloquent\Model;
use Paxifi\Support\Contracts\ValidatorInterface;

/**
 * The Base Model using Eloquent ORM
 * @package Paxifi\Support\Repository
 */
class BaseModel extends Model implements ValidatorInterface
{
    /**
     * @var array
     */
    protected $rules = [];

    /**
     * @var array
     */
    protected $errors;

    /**
     * @{@inheritdoc }
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @{@inheritdoc }
     */
    public function validate($data)
    {
        // make a new validator object
        $v = \Validator::make($data, $this->rules);

        // check for failure
        if ($v->fails()) {
            // set errors and return false
            $this->errors = $v->errors;
            return false;
        }

        // validation pass
        return true;
    }

    /**
     * @{@inheritdoc }
     */
    public function getValidationErrors()
    {
        return $this->errors;
    }
}