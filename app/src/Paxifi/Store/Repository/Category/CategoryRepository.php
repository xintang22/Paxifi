<?php namespace Paxifi\Store\Repository\Category;

use Illuminate\Support\Facades\Facade;

class CategoryRepository extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'paxifi.repository.category';
    }
} 