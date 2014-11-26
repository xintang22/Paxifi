<?php

namespace Paxifi\Support\Image\Gd;

use Intervention\Image\Gd\Color;
use Paxifi\Support\Image\Image;

class Driver extends \Intervention\Image\Gd\Driver
{

    /**
     * Creates new image instance
     *
     * @param  integer $width
     * @param  integer $height
     * @param  string  $background
     * @return \Intervention\Image\Image
     */
    public function newImage($width, $height, $background = null)
    {
        // create empty resource
        $core = imagecreatetruecolor($width, $height);
        $image = new Image(new self, $core);

        // set background color
        $background = new Color($background);
        imagefill($image->getCore(), 0, 0, $background->getInt());

        return $image;
    }
}
