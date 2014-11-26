<?php namespace Paxifi\Support\Image\Gd;

use Intervention\Image\Gd\Decoder as GdDecoder;
use Paxifi\Support\Image\Image;

class Decoder extends GdDecoder {
    /**
     * Initiates new image from GD resource
     *
     * @param  Resource $resource
     * @return \Intervention\Image\Image
     */
    public function initFromGdResource($resource)
    {
        return new Image(new Driver, $resource);
    }
} 