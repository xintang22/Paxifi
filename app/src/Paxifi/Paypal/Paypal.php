<?php namespace Paxifi\Paypal;

use GuzzleHttp\Client;
use Illuminate\Config\Repository;
use Illuminate\Http\Request;
use Paxifi\Payment\Repository\EloquentPaymentRepository;
use Paxifi\Store\Repository\Driver\DriverRepository;
use Paxifi\Store\Repository\Driver\EloquentDriverRepository;
use Paxifi\Paypal\Logger as PaypalLog;

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
     * @param bool   $attachToRequest
     *
     * @throws \InvalidArgumentException
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

        if ($res->getStatusCode() == 200) {
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
     * @param EloquentDriverRepository $driver
     *
     * @throws \Exception
     * @return string
     */
    public function getUserAccessToken(EloquentDriverRepository $driver)
    {
        try {
            $oauth2Url = $this->paypalUrl . 'oauth2/token';

            $res = $this->client->post($oauth2Url, [
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'auth' => [$this->clientId, $this->clientSecret],
                'body' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $driver->paypal_refresh_token
                ]
            ]);

            if ($res->getStatusCode() == 200) {
                return $res->json(["object" => true])->access_token;
            }

            throw new \Exception('Refresh access token failed.');
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Retrieve user profile attributes.
     *
     * @param \Paxifi\Store\Repository\Driver\EloquentDriverRepository $driver
     *
     * @return string
     */
    public function getUserInfoByAccessToken(EloquentDriverRepository $driver)
    {

        // Get access token
        $accessToken = $this->getUserAccessToken($driver);

        // Create a fake payment to check the user paypal account
        // and store his paypal email (metchant email)
        $transactions = [
            'intent' => 'authorize',
            'payer' => [
                'payment_method' => 'paypal'
            ],
            'transactions' => [
                [
                    "amount" => [
                        "currency" => 'USD',
                        "total" => 0.01
                    ],
                    "description" => "Paxifi Testing Payment"
                ]
            ]
        ];

        $payment = $this->createPayment($accessToken, $transactions);

        // Capture the payment
        $capturedPayment = $this->capturePayment($accessToken, $payment);

        // Refund the payment
        if ($this->refundPayment($accessToken, $capturedPayment)) {
            // @TODO return the correct format (array or object) of the payer info
            return $payment->payer->payer_info;
        }
    }

    /**
     * Create a payment.
     *
     * @param       $accessToken
     * @param array $transactions
     *
     * @throws \Exception
     * @return \GuzzleHttp\Message\ResponseInterface
     */
    public function createPayment($accessToken, array $transactions = array())
    {
        try {

            $paymentUrl = $this->paypalUrl . 'payments/payment';

            $res = $this->client->post($paymentUrl, [
                'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $accessToken],
                'json' => $transactions
            ]);

            if ($res->getStatusCode() == 201) {
                PaypalLog::info(['Create' => $res->json()]);
                return $res->json(['object' => true]);
            }

            PaypalLog::error(['Create future payment failed']);
            throw new \Exception('Create future payment failed');
        } catch (\Exception $e) {
            PaypalLog::error(['Create future payment failed']);
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @param $accessToken
     * @param $payment
     *
     * @return mixed
     * @throws \Exception
     */
    public function capturePayment($accessToken, $payment)
    {
        try {
            $links = $payment->transactions[0]->related_resources[0]->authorization->links;

            $captureUrl = $this->getPaypalLink($links, 'capture');

            $capture = $this->client->post($captureUrl, [
                'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $accessToken],
                'json' => [
                    'amount' => [
                        'currency' => $payment->transactions[0]->amount->currency,
                        'total' => $payment->transactions[0]->amount->total,
                    ],
                    'is_final_capture' => true
                ]
            ]);

            if ($capture->getStatusCode() == 200 && $capture->json(['object' => true])->state == "completed")
            {
                PaypalLog::info(['Capture' => $capture->json()]);
                return $capture->json(['object' => true]);
            }

            PaypalLog::error(['Capture future payment failed']);
            throw new \Exception('Capture future payment failed');
        } catch (\Exception $e) {

            PaypalLog::error(['Capture future payment failed']);
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @param $accessToken
     * @param $payment
     *
     * @return bool
     * @throws \Exception
     */
    public function refundPayment($accessToken, $payment)
    {
        try {
            $links = $payment->links;

            $refundUrl = $this->getPaypalLink($links, 'refund');

            $refund = $this->client->post($refundUrl, [
                'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $accessToken],
                'json' => ['amount' => $payment->amount]
            ]);

            if ($refund->getStatusCode() == 201) {
                PaypalLog::info(['Refund' => $refund->json()]);
                return true;
            }

            PaypalLog::error(['Refund failed']);
            return false;
        } catch (\Exception $e) {
            PaypalLog::error(['Refund failed']);
            throw new \Exception($e->getMessage());
        }

    }

    /**
     * Get paypal api link
     *
     * @param array $links
     * @param       $rel
     *
     * @return string
     */
    private function getPaypalLink(array $links = array(), $rel) {
        $matchedUrl = "";

        foreach ($links as $index => $link) {
            if ($link->rel == $rel) {
                $matchedUrl = $link->href;
            }
        }

        return $matchedUrl;
    }
}

