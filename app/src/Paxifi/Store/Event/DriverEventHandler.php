<?php namespace Paxifi\Store\Event;

use Paxifi\Store\Repository\Driver\EloquentDriverRepository;
use Config, Mail;

class DriverEventHandler {

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher $events
     * @return array
     */
    public function subscribe($events)
    {
        $events->listen('paxifi.drivers.registered', 'Paxifi\Store\Event\DriverEventHandler@onDriverRegistered');
    }

    /**
     * Handle driver registration events
     *
     * @param EloquentDriverRepository $driver
     */
    public function onDriverRegistered(EloquentDriverRepository $driver)
    {
        $email = $driver->email;
        $subject = trans('email.welcome.subject');
        $from = trans('email.welcome.from');

        Mail::queue('emails.welcome.welcome', [], function ($message) use ($email, $from, $subject) {
            $message
                ->from($from, 'Paxifi')
                ->to($email)
                ->subject($subject);
        });
    }
}