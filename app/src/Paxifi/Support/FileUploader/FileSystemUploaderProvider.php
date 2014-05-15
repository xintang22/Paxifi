<?php namespace Paxifi\Support\FileUploader;

use \Illuminate\Config\Repository as Config;

class FileSystemUploaderProvider implements UploaderProviderInterface
{
    /**
     * @var \Illuminate\Support\Facades\Config
     */
    protected $config;

    function __construct(Config $config)
    {
        $this->config = $config;
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
    public function upload($file, $target = null, $options = array())
    {
        if (is_array($file))
            return array_map(array($this, 'uploadSingleFile'), $file);

        return array($this->uploadSingleFile($file));
    }

    /**
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     *
     * @return string
     */
    protected function uploadSingleFile($file)
    {
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();

        $targetDirectory = $this->config->get('paxifi.files.uploads_directory');

        $file->move($targetDirectory, $filename);

        return $this->config->get('app.url') . '/' . basename($targetDirectory) . '/' . $filename;
    }
}