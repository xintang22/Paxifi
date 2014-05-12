<?php namespace Paxifi\Store\Controller;

use Illuminate\Routing\Controller;
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
            return array(
                'success' => 1,
                'message' => 'You have been successfully logged in.',
                '_token' => \Session::token(),
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
        Auth::logout();

        return \Response::json(array('success' => 1, 'message' => 'You have been successfully logged out.'));
    }
}