<?php namespace Paxifi\Provider;

use Illuminate\Support\ServiceProvider;

class IssueServiceProvider extends ServiceProvider {

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerRouters();

        $this->registerIssueRepository();
    }

    public function registerRouters()
    {
        // Post a new issue.
        $this->app['router']->post('issues', 'Paxifi\Issue\Controller\IssueController@store');

        // Retrieve issue types list
        $this->app['router']->get('issues/list', 'Paxifi\Issue\Controller\IssueTypeController@index');
    }

    public function registerIssueRepository()
    {
        $this->app->bind('paxifi.repository.issues', 'Paxifi\Issue\Repository\EloquentIssueRepository', true);

        $this->app->bind('paxifi.repository.issue_types', 'Paxifi\Issue\Repository\EloquentIssueTypesRepository', true);
    }

    public function provides()
    {
        return ['paxifi.repository.issues', 'paxifi.repository.issue_types'];
    }
}