<?php namespace Paxifi\Provider;

use Illuminate\Support\ServiceProvider;

class OAuth2ServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        /** @var \Illuminate\Routing\Router $router */
        $router = $this->app['router'];

        $router->filter('check-store-owner', function ($route) {
            $ownerId = \ResourceServer::getOwnerId();
            $driver = $route->getParameter('driver');

            if (!$driver or $driver->id != $ownerId) {
                return \Response::json(array(
                    'status' => 403,
                    'error' => 'forbidden',
                    'error_message' => 'You are trying to access other user\'s data.',
                ), 403);
            }

        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerRoutes();
    }

    /**
     * Register Order resource Routes
     *
     * @return void
     */
    public function registerRoutes()
    {
        $this->app['router']->post('oauth2/access_token', function () {
            return \AuthorizationServer::performAccessTokenFlow();
        });
    }
}