<?php namespace Paxifi\Store\Repository\Category;

interface CategoryRepositoryInterface
{

    /**
     * Return categories with active status.
     *
     * @return mixed
     */
    public function enabled();

} 