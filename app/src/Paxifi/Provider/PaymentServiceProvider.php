<?php namespace Paxifi\Provider;

use Illuminate\Support\ServiceProvider;
use Paxifi\Payment\Exception\PaymentNotFoundException;
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
        $this->registerCommissionCalculator();

        $this->registerPaymentRepository();

        $this->registerRouteModelBindings();

        $this->registerRoutes();

        $this->registerPaymentEvents();
    }

    /**
     * Register Payment resource Routes
     *
     * @return void
     */
    public function registerRoutes()
    {
        $this->app['router']->group(['before' => 'oauth'], function () {

            // Get payment with specific payment id.
            $this->app['router']->get('payments/{payment}', 'Paxifi\Payment\Controller\PaymentController@show');

            // Cancel order
            $this->app['router']->put('payments/{payment}/cancel', 'Paxifi\Payment\Controller\PaymentController@cancel');

            // Invoice
            $this->app['router']->post('payments/{payment}/invoice', 'Paxifi\Payment\Controller\PaymentController@invoice');

            // Feedback
            $this->app['router']->put('payments/{payment}/feedback', 'Paxifi\Feedback\Controller\FeedbackController@feedback');

            // Paypal Status check
            $this->app['router']->get('payments/{payment}/verify', 'Paxifi\Payment\Controller\PaymentController@verify');
        });
    }

    /**
     * Register model binding.
     *
     * @return void
     */
    protected function registerRouteModelBindings()
    {
        // Notification
        $this->app['router']->model('payment', 'Paxifi\Payment\Repository\EloquentPaymentRepository', function () {
            throw new PaymentNotFoundException('Payment does not exist.');
        });

        $this->app->error(function (PaymentNotFoundException $exception) {
            return \Response::json(array('error' => array(
                'context' => null,
                'message' => $exception->getMessage(),
                'code' => 404,
            )), 404);
        });
    }

    /**
     * Register the commission calculator implementation.
     *
     * @return void
     */
    protected function registerCommissionCalculator()
    {
        $this->app->bindShared('Paxifi\Support\Commission\CalculatorInterface', function ($app) {

            $commissionRate = $app->config->get('paxifi.commission.rate', 0);

            return new Calculator($commissionRate);

        });
    }

    /**
     * Register the payment repository implementation.
     *
     * @return void
     */
    protected function registerPaymentRepository()
    {
        $this->app->bind('paxifi.repository.payment', 'Paxifi\Payment\Repository\EloquentPaymentRepository', true);
        $this->app->bind('paxifi.repository.payment_methods', 'Paxifi\Payment\Repository\EloquentPaymentMethodsRepository', true);
    }

    /**
     * Register payment events
     */
    protected function registerPaymentEvents()
    {
        $this->app['events']->listen('paxifi.build.invoice' , 'Paxifi\Payment\Controller\PaymentController@buildInvoice');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('paxifi.repository.payment', 'paxifi.repository.payment_methods');
    }
}