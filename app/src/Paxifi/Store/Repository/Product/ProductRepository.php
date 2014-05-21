<?php namespace Paxifi\Store\Repository\Product;

use Illuminate\Support\Facades\Facade;

class ProductRepository extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'paxifi.repository.product';
    }
} 