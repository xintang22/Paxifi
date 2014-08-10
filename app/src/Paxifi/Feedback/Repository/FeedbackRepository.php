<?php namespace Paxifi\Feedback\Repository;

use Illuminate\Support\Facades\Facade;

class FeedbackRepository extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'paxifi.repository.feedbacks';
    }
}