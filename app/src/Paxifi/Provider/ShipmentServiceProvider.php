<?php namespace Paxifi\Provider;

use Illuminate\Support\ServiceProvider;

class ShipmentServiceProvider extends ServiceProvider {
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerShipmentRepository();
    }

    /**
     * Register the shipment repository implementation.
     *
     * @return void
     */
    public function registerShipmentRepository()
    {
        $this->app->bind('paxifi.repository.shipment', 'Paxifi\Shipment\Repository\EloquentShipmentRepository', true);
    }


    public function registerRoutes()
    {

    }


    public function provides()
    {
        return ['paxifi.repository.shipment'];
    }
}