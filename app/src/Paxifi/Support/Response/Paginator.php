<?php namespace Paxifi\Support\Response;

use League\Fractal\Pagination\IlluminatePaginatorAdapter;

class Paginator extends IlluminatePaginatorAdapter
{
    /**
     * Override the parent constructor to allow for dependency injection
     * @see \Paxifi\Support\Response\Response
     */
    function __construct()
    {
    }

    /**
     * Setup the Paginator instance.
     *
     * @param \Illuminate\Pagination\Paginator $paginator
     */
    public function setupPaginator($paginator)
    {
        $this->env = (method_exists($paginator, 'getFactory') ? $paginator->getFactory() : $paginator->getEnvironment());
        $this->items = $paginator->getItems();
        $this->total = (int) $paginator->getTotal();
        $this->perPage = (int) $paginator->getPerPage();

        $this->setupPaginationContext();
    }
}