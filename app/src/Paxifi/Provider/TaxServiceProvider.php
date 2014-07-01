<?php namespace Paxifi\Provider;

use Illuminate\Support\ServiceProvider;

class TaxServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerListeners();
    }

    /**
     * Register event listeners
     *
     * @return void
     */
    public function registerListeners()
    {
        // @TODO: Find better way to set the default tax strategy
        $this->app['events']->listen(['paxifi.store.created', 'paxifi.store.updated'], function ($driver) {
            switch ($driver->getCountry()) {
                case 'UK':
                    $driver->tax_included_in_price = true;
                    $driver->save();
                    break;
            }
        });
    }
}