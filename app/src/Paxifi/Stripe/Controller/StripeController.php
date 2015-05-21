<?php

namespace Paxifi\Stripe\Controller;

use Paxifi\Stripe\Repository\EloquentStripeRepository;
use Paxifi\Stripe\Repository\StripeRepository;
use Paxifi\Support\Controller\ApiController;
use Stripe\HttpClient\CurlClient;
use Input, Config;

class StripeController extends ApiController
{
    private $stripeClient;

    function __construct() {
        parent::__construct();
        $this->stripeClient = CurlClient::instance();
    }

    function authorize($driver = null)
    {
        try {
            if (is_null($driver)) {
                $driver = $this->getAuthenticatedDriver();
            }

            $authUrl = getenv('STRIPE_CONNECT_API') . 'oauth/token';

            $params = [
                'client_secret' => getenv('STRIPE_SECRET_KEY'),
                'code' => Input::get('code'),
                'grant_type' => 'authorization_code'
            ];

            $response = $this->stripeClient->request('POST', $authUrl, [], $params, false);

            if ($response[1] == '200') {
                $data = json_decode($response[0], true);

                $data['driver_id'] = $driver->id;

                if ($stripe = EloquentStripeRepository::create($data)) {
                    $driver->enableStripe();
                    return $this->setStatusCode(200)->respondWith($data);
                }
            } else {
                return $this->setStatusCode($response[1])->respondWithError(json_decode($response[0])->error);
            }
        } catch (\Exception $e) {
            return $this->errorInternalError($e->getMessage());
        }

    }

    /**
     * Retrieves the Data Transformer
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function getTransformer()
    {
        // TODO: Implement getTransformer() method.
    }
}