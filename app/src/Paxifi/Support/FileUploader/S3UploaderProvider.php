<?php namespace Paxifi\Support\FileUploader;

use GrahamCampbell\Flysystem\FlysystemManager;

class S3UploaderProvider implements UploaderProviderInterface {

    /**
     * @var FlysystemManager
     */
    protected $flysystem;

    function __construct(FlysystemManager $flysystem)
    {
        $this->flysystem = $flysystem;
    }

    /**
     * Upload a file to target and return a full URL
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile|array $file
     * @param string $target
     * @param array $options
     *
     * @return bool|string
     */
    public function upload($file, $target = 'photos', $options = array())
    {
        if (is_array($file))
            return array_map(array($this, 'uploadSingleFile'), $file);

        return array($this->uploadSingleFile($file, $target));
    }

    /**
     * Upload a single file.
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     *
     * @param string $target
     * @return string
     */
    protected function uploadSingleFile($file, $target = null)
    {
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();

        $filename = $target ? $target . '/' . $filename : $filename;

        $content = file_get_contents($file->getRealPath());

        $this->flysystem->write($filename, $content);

        return cloudfront_asset($filename);
    }
}