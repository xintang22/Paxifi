<?php namespace Paxifi\Paypal;

use GuzzleHttp\Client;
use Illuminate\Config\Repository;
use Illuminate\Http\Request;
use Paxifi\Store\Repository\Driver\EloquentDriverRepository;
use Paxifi\Paypal\Logger as PaypalLog;
use Paxifi\Subscription\Repository\EloquentPlanRepository;

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
     * Get paypal api link
     *
     * @param array $links
     * @param       $rel
     *
     * @return string
     */
    private function getPaypalLink(array $links = array(), $rel)
    {
        $matchedUrl = "";

        foreach ($links as $index => $link) {
            if ($link->rel == $rel) {
                $matchedUrl = $link->href;
            }
        }

        return $matchedUrl;
    }

    /**
     * Get the authorize paypal transaction object.
     *
     * @param float  $amount
     * @param string $currency
     *
     * @param string $description
     *
     * @return array
     */
    public function getFuturePaymentTransaction($amount = 0.01, $currency = 'USD', $description = 'Paxifi PayPal Transaction') {
        $transaction = [
            'intent' => 'authorize',
            'payer' => [
                'payment_method' => 'paypal'
            ],
            'transactions' => [
                [
                    "amount" => [
                        "currency" => $currency,
                        "total" => $amount
                    ],
                    "description" => $description
                ]
            ]
        ];

        return $transaction;
    }

    /**
     * Curl PayPal request.
     *
     * @param        $url
     * @param string $method
     * @param null   $postvals
     *
     * @return array
     */
    private function curl($url, $method = 'GET', $postvals = null)
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $this->clientId . ":" . $this->clientSecret);

        $options = array(
            CURLOPT_HEADER => true,
            CURLINFO_HEADER_OUT => true,
            CURLOPT_HTTPHEADER => array("Accept: application/json", "Accept-Language: en_US"),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_VERBOSE => true,
            CURLOPT_POSTFIELDS => $postvals,
            CURLOPT_CUSTOMREQUEST => $method,
        );

        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);

        $statue = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $header = substr($response, 0, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
        $body = json_decode(substr($response, curl_getinfo($ch, CURLINFO_HEADER_SIZE)));

        curl_close($ch);

        return array('status' => $statue, 'header' => $header, 'body' => $body);
    }

    /**
     * Create a payment.
     *
     * @param       $accessToken
     * @param array $transactions
     *
     * @param null  $driver
     *
     * @throws \Exception
     * @return \GuzzleHttp\Message\ResponseInterface
     */
    public function createPayment($accessToken = null, array $transactions = array(), $driver = null)
    {
        try {
            $accessToken = is_null($accessToken) ? $this->getUserAccessToken($driver) : $accessToken;

            $paymentUrl = $this->paypalUrl . 'payments/payment';

            if ($driver->paypal_metadata_id) {
                $res = $this->client->post($paymentUrl,
                    ['headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $accessToken,
                        'PayPal-Client-Metadata-Id' => $driver->paypal_metadata_id
                    ],
                        'json' => $transactions
                    ]);
            } else {
                $res = $this->client->post($paymentUrl,
                    ['headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $accessToken,
                    ],
                        'json' => $transactions
                    ]);
            }

            if ($res->getStatusCode() == 201) {

                // Todo:: record create authorized future payment success.
                return $res->json(['object' => true]);
            } else {

                // Todo:: record create authorized future payment failed.
                return false;
            }

        } catch (\Exception $e) {

            // Todo:: record create authorized future payment failed.
            return false;

        }
    }

    /**
     * @param      $accessToken
     * @param      $payment
     *
     * @param null $driver
     *
     * @throws \Exception
     * @return mixed
     */
    public function capturePayment($accessToken = null, $payment, $driver = null)
    {
        try {
            $accessToken = is_null($accessToken) ? $this->getUserAccessToken($driver) : $accessToken;

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

            if ($capture->getStatusCode() == 200) {

                if (($capture->json(['object' => true])->state == 'pending' &&
                        $payment->transactions[0]->related_resources[0]->authorization->state == 'pending' &&
                        $payment->transactions[0]->related_resources[0]->authorization->reason_code == 'PAYMENT_REVIEW') ||
                    $capture->json(['object' => true])->state == "completed"
                ) {
                    // Todo:: record capture payment success.

                    return $capture->json(['object' => true]);
                }

                // Todo:: record capture payment failed.
            }

            // Todo:: record capture payment error.
        } catch (\Exception $e) {

            // Todo:: record capture payment failed.

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
                // Todo:: record refund payment success.
                return true;
            }
            // Todo:: record refund payment failed.

            return false;
        } catch (\Exception $e) {

            // Todo:: record refund payment failed.

        }

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

         return $this->requestWithCurl($code, $attachToRequest, $oauth2Url);

//        return $this->requestWithGuzzle($code, $attachToRequest, $oauth2Url);

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

            $postvals = "grant_type=refresh_token&refresh_token=$driver->paypal_refresh_token";

            $response = self::curl($oauth2Url, "POST", $postvals);

            if ($response['status'] == 200) {
                return $response['body']->access_token;
            }

            // Todo:: record get user access token failed.
        } catch (\Exception $e) {
            // Todo:: record get user access token failed.
        }
    }

    /**
     * Retrieve user profile attributes by given access token.
     *
     * @param $accessToken
     *
     * @return mixed
     */
    public function getUserInfoByAccessToken($accessToken, $driver)
    {

        // Create a fake payment to check the user PayPal account
        // and store his PayPal email (merchant email)
        $transaction = $this->getFuturePaymentTransaction(0.01, 'USD', 'Paxifi: check validity of PayPal account');

        $payment = $this->createPayment($accessToken, $transaction, $driver);

        // Capture the payment
        $capturedPayment = $this->capturePayment($accessToken, $payment);

        // Refund the payment
        if ($this->refundPayment($accessToken, $capturedPayment)) {
            return $payment->payer->payer_info;
        } else {
            return fasle;
        }
    }

    /**
     * create subscription payment via paypal.
     *
     * @param EloquentPlanRepository   $plan
     * @param EloquentDriverRepository $driver
     *
     * @return bool|mixed
     */
    public function subscriptionPayment(EloquentPlanRepository $plan, EloquentDriverRepository $driver)
    {

        $transaction = $this->getFuturePaymentTransaction($plan->amount, $plan->currency, "Paxifi: Subscription payment.");

        if ($payment = $this->createPayment(null, $transaction, $driver)) {
            // Todo:: record create subscription payment success.

            // Capture the payment
            if ($capturedPayment = $this->capturePayment(null, $payment, $driver)) {
                // Todo:: record capture subscription payment success.

                return $capturedPayment;
            } else {
                // Todo:: record capture subscription payment failed.
                return false;
            }
        }

        // Todo:: record create subscription payment failed.
    }

    /**
     * @param                          $commission
     * @param EloquentDriverRepository $driver
     *
     * @internal param $sales
     * @return bool|mixed
     */
    public function commissionPayment($commission, EloquentDriverRepository $driver) {
        $transaction = $this->getFuturePaymentTransaction($commission, $driver->currency, "Paxifi: Commission payment.");

        $payment = $this->createPayment(null, $transaction, $driver);

        // Capture the payment
        if ($capturedPayment = $this->capturePayment(null, $payment, $driver)) {
            // Todo:: record capture commission payment success.
            return $capturedPayment;
        } else {
            // Todo:: record capture comission payment failed.
            return false;
        }
    }

    /**
     * @param EloquentDriverRepository $driver
     *
     * @return bool|mixed
     */
    public function buySticker(EloquentDriverRepository $driver) {
        $transaction = $this->getFuturePaymentTransaction($driver->getStickerPrice(), $driver->currency, "Sticker Payment");

        $payment = $this->createPayment(null, $transaction, $driver);

        // Capture the payment
        if ($capturedPayment = $this->capturePayment(null, $payment, $driver)) {
            // Todo:: record capture purchase sticker payment success.
            return $capturedPayment;
        } else {
            // Todo:: record capture purchase sticker payment failed.
            return false;
        }
    }

    /**
     * @param $code
     * @param $attachToRequest
     * @param $oauth2Url
     *
     * @throws \InvalidArgumentException
     * @return mixed
     */
    private function requestWithGuzzle($code, $attachToRequest, $oauth2Url)
    {
        try {
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
                    $this->request->merge(['paypal' => $res->json(['object' => true])]);
                }

                return $res->json();
            }
            throw new \InvalidArgumentException('Invalid Paypal Authorization Code.');

        } catch (\Exception $e) {

            throw new \InvalidArgumentException('Invalid Paypal Authorization Code.');

        }

    }

    /**
     * @param $code
     * @param $attachToRequest
     * @param $oauth2Url
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    private function requestWithCurl($code, $attachToRequest, $oauth2Url)
    {
        $postvals = "grant_type=authorization_code&response_type=token&redirect_uri=urn:ietf:wg:oauth:2.0:oob&code=$code";

        $response = self::curl($oauth2Url, "POST", $postvals);

        if ($response['status'] == 200) {
            if ($attachToRequest) {
                // Attach the user paypal authorization to the Request
                $this->request->merge(['paypal' => $response['body']]);
            }

            return $response['body'];
        }

        throw new \InvalidArgumentException('Invalid Paypal Authorization Code.');
    }
}

