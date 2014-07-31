<?php namespace Paxifi\Issue\Repository;

use Illuminate\Support\Facades\Facade;

class IssueTypesRepository extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'paxifi.repository.issue_types';
    }
}