<?php namespace Paxifi\Support\FileUploader;

class S3UploaderProvider implements UploaderProviderInterface
{
    /**
     * Upload a file to target and return a full URL
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile|array $file
     * @param string $target
     * @param array $options
     *
     * @return bool|string
     */
    public function upload($file, $target = null, $options = array())
    {
        // TODO: Implement upload() method.
    }
}