<?php namespace Paxifi\Store\Repository\Product;

use Paxifi\Support\Repository\BaseModel;

class EloquentProductRepository extends BaseModel implements ProductRespositoryInterface
{

    protected $table = 'products';

} 