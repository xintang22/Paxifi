<?php namespace Paxifi\Provider;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;
use Paxifi\Support\FileUploader\FileSystemUploaderProvider;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

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

        $this->registerUploaderConfiguration();

        $this->registerErrorHandlers();
    }

    /**
     * Register file upload route
     *
     * @return void
     */
    protected function registerRoutes()
    {
        $this->app['router']->group(['before' => 'oauth'], function () {
            $this->app['router']->post('files', 'Paxifi\Support\Controller\FilesController@upload');
        });
    }

    /**
     * Register the file uploader implementation.
     *
     * @return void
     */
    protected function registerFileUploaderProvider()
    {
        $this->app->bindShared('paxifi.files.uploader', function ($app) {
            return new FileSystemUploaderProvider($app['config']);
        });
    }

    /**
     * Register the Uploader configurations.
     *
     * @return void
     */
    protected function registerUploaderConfiguration()
    {
        $this->app['config']->set('paxifi.files.uploads_directory', 'public/uploads');
    }

    /**
     * Register error handlers.
     *
     * @return void
     */
    protected function registerErrorHandlers()
    {
        $this->app->error(function (FileException $exception) {
            return Response::json(array(
                'error' => 1,
                'message' => $exception->getMessage(),
            ), 400);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('paxifi.files.uploader');
    }
}