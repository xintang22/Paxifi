<?php namespace Paxifi\Provider;

use Illuminate\Support\ServiceProvider;

class ProblemServiceProvider extends ServiceProvider {

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerRoutes();

        $this->registerProblemRepository();
    }

    /**
     * Register Problems resource Routes
     *
     * @return void
     */
    public function registerRoutes()
    {
        $this->app['router']->group(['before' => 'oauth'], function () {
            // Get product problems.
            $this->app['router']->get('problem_types', 'Paxifi\Store\Controller\ProductController@problems');

            // Report Problem
            $this->app['router']->post('problems', 'Paxifi\Store\Controller\ProductController@problem');
        });
    }
    /**
     *
     */
    public function registerProblemRepository()
    {
        $this->app->bind('paxifi.repository.problem', 'Paxifi\Problem\Repository\EloquentProblemRepository');

        $this->app->bind('paxifi.repository.problem_type', 'Paxifi\Problem\Repository\EloquentProblemTypesRepository');
    }

    /**
     * @return array
     */
    public function provides()
    {
        return ['paxifi.repository.problem', 'paxifi.repository.problem_type'];
    }
}