<?php namespace Paxifi\Provider;

use Illuminate\Support\ServiceProvider;

class ProductServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerRoutes();

        $this->registerCategoryRepository();
    }

    /**
     * Register the category repository implementation.
     *
     * @return void
     */
    protected function registerCategoryRepository()
    {
        $this->app->bind('paxifi.repository.category', 'Paxifi\Store\Repository\Category\EloquentCategoryRepository', true);
    }

    /**
     * Register Product resource Routes
     *
     * @return void
     */
    public function registerRoutes()
    {
        $this->app['router']->get('products/categories', 'Paxifi\Store\Controller\CategoryController@index');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('paxifi.repository.category');
    }
}