<?php namespace Paxifi\Support\Image;

use GrahamCampbell\Flysystem\Facades\Flysystem;
use Intervention\Image\AbstractDriver;
use Intervention\Image\Image as InterventionImage;

/**
 * Class Image extends the intervention image.
 * @package Paxifi\Support\Image
 */
class Image extends InterventionImage
{

    public function __construct(AbstractDriver $driver = null, $core = null)
    {
        parent::__construct($driver, $core);
    }

    /**
     * Add method to save file to S3 server.
     *
     * @param null $path
     * @param null $quality
     * @return $this
     */
    public function saveToS3($path = null, $quality = null)
    {
        $path = is_null($path) ? $this->basePath() : $path;

        $data = $this->encode(pathinfo($path, PATHINFO_EXTENSION), $quality);

        Flysystem::put($path, $data);

        return $this;
    }
}