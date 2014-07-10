<?php namespace Paxifi\Sticker\Repository\Validation;

use Paxifi\Support\Validation\Validator;

class UpdateStickerValidator extends Validator
{
    /**
     * @var array
     */
    protected $rules = [
        'driver_id' => 'unique:stickers,driver_id|exists:drivers,id',
        'email'     => 'sometimes|required|email',
        'image'     => 'url'
    ];
} 