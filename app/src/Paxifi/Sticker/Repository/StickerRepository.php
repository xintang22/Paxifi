<?php namespace Paxifi\Sticker\Repository;

use Illuminate\Support\Facades\Facade;

class StickerRepository extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'paxifi.repository.sticker';
    }
} 