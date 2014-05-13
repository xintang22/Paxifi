<?php namespace Paxifi\Support\FileUploader;

interface UploaderProviderInterface
{
    /**
     * File upload handler.
     *
     * @param       $source
     * @param       $destination
     * @param array $options
     *
     * @return mixed
     */
    public function upload($source, $destination, $options = array());
}