<?php namespace Paxifi\Store\Controller;

use Illuminate\Support\Facades\Event;
use Paxifi\Store\Auth\Auth;
use Paxifi\Support\Controller\BaseApiController;

class AuthController extends BaseApiController
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

            return $this->respond(array(
                'success' => true,
                'message' => $this->translator->trans('responses.auth.login'),
                'access_token' => \Session::token(),
            ));
        }

        return $this->errorForbidden($this->translator->trans('responses.auth.wrong_credentials'));
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

            return $this->respond(array(
                'success' => true,
                'message' => $this->translator->trans('responses.auth.logout'),
            ));
        }

        return $this->errorForbidden($this->translator->trans('responses.auth.not_logged_in'));
    }
}