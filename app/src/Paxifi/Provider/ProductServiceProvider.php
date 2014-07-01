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

        $this->registerListeners();
    }

    /**
     * Register the category repository implementation.
     *
     * @return void
     */
    protected function registerCategoryRepository()
    {
        $this->app->bind('paxifi.repository.category', 'Paxifi\Store\Repository\Category\EloquentCategoryRepository', true);

        $this->app->bind('paxifi.repository.product', 'Paxifi\Store\Repository\Product\EloquentProductRepository', true);

        $this->app->bind('paxifi.repository.product.cost', 'Paxifi\Store\Repository\Product\Cost\EloquentCostRepository', true);
    }

    /**
     * Register Product resource Routes
     *
     * @return void
     */
    public function registerRoutes()
    {
        $this->app['router']->get('products/categories', 'Paxifi\Store\Controller\CategoryController@index');

        // CRUD
        $this->app['router']->get('products', 'Paxifi\Store\Controller\ProductController@index');
    }

    /**
     * Register Product listeners
     *
     * @return void
     */
    public function registerListeners()
    {
        // @TODO: Update the product inventory after success payment.
        $this->app['events']->listen('paxifi.product.ordered', function ($product, $quantity) {
            $product->updateInventory($quantity);
            $product->save();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('paxifi.repository.category', 'paxifi.repository.product', 'paxifi.repository.product.cost');
    }
}