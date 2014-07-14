<?php namespace Paxifi\Provider;

use Illuminate\Support\ServiceProvider;
use Paxifi\Order\Exception\OrderNotFoundException;

class OrderServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerRoutes();

        $this->registerRouteModelBindings();

        $this->registerEvent();
    }

    /**
     * Register model binding.
     *
     * @return void
     */
    protected function registerRouteModelBindings()
    {
        $this->app['router']->model('order', 'Paxifi\Order\Repository\EloquentOrderRepository', function () {
            throw new OrderNotFoundException('Order does not exist.');
        });

        $this->app->error(function (OrderNotFoundException $exception) {
            return Response::json(array('error' => array(
                'context' => null,
                'message' => 'Invalid order id.',
                'code' => 400,
            )), 400);
        });
    }

    /**
     * Register Order resource Routes
     *
     * @return void
     */
    public function registerRoutes()
    {
        $this->app['router']->group(['before' => 'oauth'], function () {
            $this->app['router']->post('orders', 'Paxifi\Order\Controller\OrderController@store');

            // Invoice
            $this->app['router']->post('orders/{order}/email', 'Paxifi\Order\Controller\OrderController@email');
        });
    }

    /**
     * Boot Order service provider
     */
    public function boot()
    {
        parent::boot();

        $this->registerConfiguration();
    }

    /**
     * Register the file path configuration
     */
    public function registerConfiguration()
    {
        $config = [
            "pdf.invoices" => "pdf/invoices/",
            "images.invoices.template" => "images/invoices/template/"
        ];

        array_walk($config, function($value, $key) {
            $this->app['config']->set($key, $value);

            $path = str_replace('/', DIRECTORY_SEPARATOR, public_path($value));

            if (!file_exists($path) && !is_dir($path)) {
                mkdir($path, 0755, true);
            }
        });
    }

    /**
     * Register order events.
     */
    public function registerEvent()
    {
        \Event::listen('email.invoice', function ($emailOptions) {
            \Queue::push('Paxifi\Order\Queue\OrderQueues@email', $emailOptions);
        });
    }
}