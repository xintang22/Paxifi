<?php namespace Paxifi\Support\Socket;

use Illuminate\Support\ServiceProvider;

class SocketServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerPusher();

        $this->registerCommands();
    }

    /**
     * Register the pusher class
     *
     * @return void
     */
    protected function registerPusher()
    {
        $this->app['socket_server'] = $this->app->share(function ($app) {

            $server = new SocketServer($app, new ZmqPusher());

            return $server;

        });
    }

    /**
     * Register socket listener.
     *
     * @return void
     */
    protected function registerCommands()
    {
        $this->app['command.socket.listen'] = $this->app->share(function()
        {
            return new ListenCommand();
        });

        $this->commands([
            'command.socket.listen',
        ]);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('socket_server');
    }


}
