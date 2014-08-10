<?php namespace Paxifi\Provider;

use Illuminate\Support\ServiceProvider;

class FeedbackServiceProvider extends ServiceProvider {

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->bindFeedbackRepository();
    }

    /**
     *
     */
    public function bindFeedbackRepository()
    {
        $this->app->bind('paxifi.repository.feedbacks', 'Paxifi\Feedback\Repository\EloquentFeedbackRepository', true);
    }

    /**
     * @return array
     */
    public function provides()
    {
        return array('paxifi.repository.feedbacks');
    }
}