<?php namespace Paxifi\Provider;

use Illuminate\Support\ServiceProvider;

class ApiServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerApiConfiguration();
    }

    /**
     * Register the API default configurations.
     *
     * @return void
     */
    protected function registerApiConfiguration()
    {
        // Resource Pagination Configuration
        $this->app['config']->set('paxifi.api.pagination.count.default', 10);

        $this->app['config']->set('paxifi.api.pagination.enabled', true);

        $this->app['config']->set('paxifi.api.pagination.cursor.enabled', false);
    }
}