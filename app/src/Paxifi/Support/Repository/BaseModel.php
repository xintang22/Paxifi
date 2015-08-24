<?php namespace Paxifi\Support\Repository;

use Illuminate\Database\Eloquent\Model;
use Paxifi\Support\ModelObserver\ModelObserver;

/**
 * The Base Model using Eloquent ORM
 * @package Paxifi\Support\Repository
 */
class BaseModel extends Model
{
    public static function boot()
    {
        parent::boot();

        self::observe(new ModelObserver());
    }
}