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
        /** @var $validator \Illuminate\Validation\Validator */
        $validator = \Validator::make($data, $this->rules);

        // check for failure
        if ($validator->fails()) {
            // set errors and return false
            $this->errors = $validator->errors()->all();
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

    /**
     * Save a new model and return the instance.
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public static function create(array $attributes)
    {
        $model = new static($attributes);

        if ($model->validate($attributes))
            return parent::create($attributes);

        return false;
    }
}