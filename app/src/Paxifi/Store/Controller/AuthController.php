<?php namespace Paxifi\Store\Controller;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Event;
use Paxifi\Store\Auth\Auth;

class AuthController extends Controller
{
    /**
     * Login the driver.
     *
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = array(
            'email' => \Input::get('email'),
            'password' => \Input::get('password'),
        );

        if (Auth::attempt($credentials)) {

            Event::fire('driver.login', array(Auth::user()));

            return array(
                'success' => true,
                'message' => 'You have been successfully logged in.',
                'access_token' => \Session::token(),
            );
        }

        return \Response::json(array('error' => 1, 'message' => 'Wrong email or password'), 401);
    }

    /**
     * Logout the driver.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        if ($driver = Auth::user()) {

            Event::fire('driver.logout', array($driver));

            Auth::logout();

            return \Response::json(array('success' => true, 'message' => 'You have been successfully logged out.'));
        }

        return \Response::json(array('error' => true, 'message' => 'You are not logged in.'), 404);
    }
}