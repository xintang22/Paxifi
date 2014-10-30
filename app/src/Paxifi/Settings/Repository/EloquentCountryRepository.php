<?php namespace Paxifi\Settings\Repository;


use Paxifi\Support\Repository\BaseModel;

class EloquentCountryRepository extends BaseModel
{
    /**
     * @var string
     */
    protected $table = 'countries';

    /**
     * @var bool
     */
    public $timestamps = false;

} 