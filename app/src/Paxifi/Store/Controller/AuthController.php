<?php namespace Paxifi\Store\Controller;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Event;
use Illuminate\Translation\Translator;
use Paxifi\Store\Auth\Auth;

class AuthController extends Controller
{
    /**
     * The Translator implementation.
     *
     * @var \Illuminate\Translation\Translator
     */
    protected $translator;

    function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

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

            return \Response::json(array(
                'success' => true,
                'message' => $this->translator->trans('responses.auth.success.login'),
                'access_token' => \Session::token(),
            ));
        }

        return \Response::json(array(
            'error' => true,
            'message' => $this->translator->trans('responses.auth.error.wrong_credentials')
        ), 401);
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

            return \Response::json(array(
                'success' => true,
                'message' => $this->translator->trans('responses.auth.success.logout'),
            ));
        }

        return \Response::json(array(
            'error' => true,
            'message' => $this->translator->trans('responses.auth.error.not_logged_in'),
        ), 403);
    }
}