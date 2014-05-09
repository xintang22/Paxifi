<?php namespace Paxifi\Store\Repository;

use Paxifi\Support\Repository\BaseModel;

class EloquentDriverRepository extends BaseModel implements DriverRepositoryInterface
{
    protected $table = 'drivers';
}