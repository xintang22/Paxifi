<?php namespace Paxifi\Problem\Repository;

use Illuminate\Support\Facades\Facade;

class ProblemTypesRepository extends Facade {
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'paxifi.repository.problem_type';
    }
} 