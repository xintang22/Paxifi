<?php namespace Paxifi\Provider;

use Illuminate\Support\ServiceProvider;
use Paxifi\Support\FileUploader\FileSystemUploaderProvider;

class FileUploaderServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerRoutes();

        $this->registerFileUploaderProvider();
    }

    /**
     * Register file upload route
     *
     * @return void
     */
    protected function registerRoutes()
    {
        $this->app['router']->post('files', 'Paxifi\Support\Controller\FilesController@upload');
    }

    /**
     * Register the file uploader implementation.
     *
     * @return void
     */
    protected function registerFileUploaderProvider()
    {
        $this->app->bindShared('paxifi.files.uploader', function () {
            return new FileSystemUploaderProvider();
        });
    }
}