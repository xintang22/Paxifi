<?php

namespace Paxifi\Support\Image\Facades;

use Illuminate\Support\Facades\Facade;


class Image extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'paxifi.image';
    }

}