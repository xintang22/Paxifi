<?php namespace Paxifi\Paypal;

use GuzzleHttp\Client;
use Illuminate\Config\Repository;
use Illuminate\Http\Request;
use Paxifi\Payment\Repository\EloquentPaymentRepository;
use Paxifi\Store\Repository\Driver\DriverRepository;

class Paypal
{
    private $clientId;
    private $clientSecret;
    private $paypalUrl;

    private $client;
    private $config;
    private $request;

    function __construct(Client $client, Repository $config, Request $request)
    {
        $this->client = $client;
        $this->config = $config;

        $this->paypalUrl = $this->config->get('paxifi.paypal.url');
        $this->clientId = $this->config->get('paxifi.paypal.client_id');
        $this->clientSecret = $this->config->get('paxifi.paypal.client_secret');
        $this->request = $request;
    }

    /**
     * Verify given authorization code.
     *
     * @param string $code
     * @param bool $attachToRequest
     * @return mixed
     */
    public function verifyAuthorizationCode($code, $attachToRequest = false)
    {
        $oauth2Url = $this->paypalUrl . 'oauth2/token';

        /** @var \GuzzleHttp\Message\ResponseInterface $res */
        $res = $this->client->post($oauth2Url, [
            'auth' => [$this->clientId, $this->clientSecret],
            'body' => [
                'grant_type' => 'authorization_code',
                'response_type' => 'token',
                'redirect_uri' => 'urn:ietf:wg:oauth:2.0:oob',
                'code' => $code
            ]
        ]);

        if ($res->getStatusCode(200)) {
            if ($attachToRequest) {
                // Attach the user paypal authorization to the Request
                $this->request->merge(['paypal' => $res->json()]);
            }

            return $res->json();
        }

        throw new \InvalidArgumentException('Invalid Paypal Authorization Code.');
    }

    /**
     * Get the user access token using his stored refresh token
     *
     * @param DriverRepository $driver
     * @return string
     */
    public function getUserAccessToken(DriverRepository $driver)
    {
        //@TODO implement this method.
    }

    /**
     * Retrieve user profile attributes.
     *
     * @param \Paxifi\Store\Repository\Driver\DriverRepository $driver
     * @return string
     */
    public function getUserInfoByAccessToken(DriverRepository $driver)
    {
        // Get access token
        $accessToken = $this->getUserAccessToken($driver);

        // Create a fake payment to check the user paypal account
        // and store his paypal email (metchant email)
        $payment = $this->createPayment($accessToken, []);

        // Capture the payment
        $this->capturePayment($driver, $payment);

        // Refund the payment
        $this->refundPayment($driver, $payment);

        // @TODO return the correct format (array or object) of the payer info
        return $payment->payer_info;
    }

    /**
     * Create a payment.
     *
     * @param DriverRepository $driver
     * @param array $transactions
     * @return \GuzzleHttp\Message\ResponseInterface
     */
    public function createPayment(DriverRepository $driver, array $transactions = array())
    {
        //@TODO implement this method.
    }

    /**
     * @param DriverRepository $driver
     * @param EloquentPaymentRepository $payment
     * @return EloquentPaymentRepository
     */
    public function capturePayment(DriverRepository $driver, EloquentPaymentRepository $payment)
    {
        //@TODO implement this method.
    }

    /**
     * @param DriverRepository $driver
     * @param EloquentPaymentRepository $payment
     * @return bool
     */
    public function refundPayment(DriverRepository $driver, EloquentPaymentRepository $payment)
    {
        //@TODO implement this method.
    }
}

