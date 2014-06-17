<?php namespace Paxifi\Support\Validation;

use Illuminate\Database\Eloquent\Model;

class RepositoryObserverValidator extends Validator
{
    /**
     * @var array
     */
    protected $onCreateRules = [];
    /**
     * @var array
     */
    protected $onUpdateRules = [];


    /**
     * @param Model $repository
     *
     * @return mixed
     */
    public function creating(Model $repository)
    {
        $this->setValidationRules($this->onCreateRules);

        return $this->validate($repository->getAttributes());
    }

    /**
     * @param Model $repository
     *
     * @return mixed
     */
    public function updating(Model $repository)
    {
        $this->setValidationRules($this->onUpdateRules);

        return $this->validate($repository->getAttributes());
    }

}