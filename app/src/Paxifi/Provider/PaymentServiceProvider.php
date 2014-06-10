<?php namespace Paxifi\Provider;

use Illuminate\Support\ServiceProvider;
use Paxifi\Support\Commission\Calculator;

class PaymentServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerCommissionRate();

        $this->registerCommissionCalculator();
    }

    /**
     * Register the commission rate.
     *
     * @return void
     */
    protected function registerCommissionRate()
    {
        $this->app['config']->set('paxifi.commission.rate', 0.05);
    }

    /**
     * Register the commission calculator implementation.
     *
     * @return void
     */
    protected function registerCommissionCalculator()
    {
        $this->app->bindShared('Paxifi\Support\Commission\CalculatorInterface', function ($app) {

            $commissionRate = $app->config->get('paxifi.commission.rate', 0.05);

            return new Calculator($commissionRate);

        });
    }
}