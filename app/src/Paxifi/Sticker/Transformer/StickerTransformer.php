<?php namespace Paxifi\Sticker\Transformer;

use League\Fractal\TransformerAbstract;
use Paxifi\Sticker\Repository\EloquentStickerRepository;

class StickerTransformer extends TransformerAbstract {
    public function transform(EloquentStickerRepository $sticker)
    {
        return array(
            'image' => $sticker->getAttribute('image'),
            'pdf' => $sticker->getAttribute('pdf'),
        );
    }
}