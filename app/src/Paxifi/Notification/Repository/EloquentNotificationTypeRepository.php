<?php

namespace Paxifi\Notification\Repository;

use Paxifi\Support\Repository\BaseModel;

class EloquentNotificationTypeRepository extends BaseModel
{
    /**
     * @var string
     */
    protected $table = "notification_types";

    /**
     * @var array
     */
    protected $fillable = ['type', 'name',];

    /**
     * Find notification type by type.
     *
     * @param $type
     * @return mixed
     */
    public static function findByType($type)
    {
        return static::where('type', '=', strtolower($type))->first();
    }
}