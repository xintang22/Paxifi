<?php namespace Paxifi\Support\FileUploader;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Paxifi\Support\FileUploader\FileSystemUploaderProvider
 */
class FileUploader extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'file.uploader';
    }
} 