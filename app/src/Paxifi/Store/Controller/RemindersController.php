<?php namespace Paxifi\Store\Controller;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;
use Paxifi\Store\Auth\Password;

/**
 * Class RemindersController
 * @package Paxifi\Store\Controller
 */
class RemindersController extends Controller
{

    /**
     * Send the password reminder.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function remind()
    {
        $response = Password::remind(\Input::only('email'), function ($message) {
            $message->subject('Paxifi | Password Reminder');
        });

        switch ($response) {
            case Password::INVALID_USER:
                return Response::json(array('error' => 1, 'message' => 'Driver does not exist.'), 400);

            case Password::REMINDER_SENT:
                return Response::json(array('success' => 1, 'message' => sprintf('Mail has been sent to %s', \Input::get('email'))));
        }
    }

    /**
     * Display the password reset view for the given token.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\Response
     */
    public function show($token = null)
    {
        if (is_null($token)) \App::abort(404);

        return \View::make('store.password.reset')->with('token', $token);
    }

    /**
     * Handle a POST request to reset a user's password.
     *
     * @return Response
     */
    public function reset()
    {
        $credentials = \Input::only(
            'email', 'password', 'password_confirmation', 'token'
        );

        $response = Password::reset($credentials, function ($driver, $password) {
            $driver->password = $password;

            $driver->save();
        });

        switch ($response) {
            case Password::INVALID_PASSWORD:
            case Password::INVALID_TOKEN:
            case Password::INVALID_USER:
                return \Redirect::back()->with('error', \Lang::get($response));

            case Password::PASSWORD_RESET:
                return \Redirect::back()->with('success', 'Your password has been successfully updated.');
        }
    }

}
