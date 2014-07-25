<?php namespace Paxifi\Sticker\Transformer;

use League\Fractal\TransformerAbstract;
use Paxifi\Sticker\Repository\EloquentStickerRepository;

class StickerTransformer extends TransformerAbstract {

    protected $fee = [
        'UK' => 4,
        'US' => 5
    ];

    public function transform(EloquentStickerRepository $sticker)
    {


        return array(
            'image' => $sticker->getAttribute('image'),
            'pdf' => $sticker->getAttribute('pdf'),
            'currency' => $sticker->driver->currency,
            'shipment_fee' => $this->fee[$sticker->driver->address['country']]
        );
    }
}