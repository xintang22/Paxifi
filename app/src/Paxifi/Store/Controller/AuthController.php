<?php namespace Paxifi\Store\Controller;

use DB;
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
        return \AuthorizationServer::performAccessTokenFlow();
    }

    /**
     * Logout the driver.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        try {
            $ownerId = \ResourceServer::getOwnerId();

            DB::table('oauth_sessions')
                ->where('oauth_sessions.owner_id', '=', $ownerId)
                ->delete();

            return $this->respond([])->setStatusCode(204);

        } catch (\Exception $e) {
            return $this->errorInternalError($e->getMessage());
        }
    }
}