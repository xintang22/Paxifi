<?php
namespace Paxifi\Paypal\Helper;


class PaypalHelper
{

    protected $client;

    protected $headers = [];

    protected $driver;

    protected $access_token = "";

    protected $client_access_token = "";

    // paypal helper initialize
    public function __construct($driver)
    {
        $this->client = new \GuzzleHttp\Client();
        $this->driver = $driver;
        $this->access_token = $this->refreshAccessToken();
        $this->setAuthorizationHeader();
    }

    /**
     * set the authorization header.
     */
    public function setAuthorizationHeader()
    {
        $this->headers = array_merge($this->headers, ['Authorization' => 'Bearer ' . $this->access_token]);
    }

    /**
     * @param $headers
     */
    protected function setHeaders($headers)
    {
        $this->headers = $headers ? array_merge($this->headers, $headers) : ['Content-Type' => 'application/x-www-form-urlencoded'];
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Capture the commission from drivers.
     *
     * @param $authorizedPayment
     *
     * @return bool|\GuzzleHttp\Message\ResponseInterface
     */
    public function capturePaypalPayment($authorizedPayment)
    {
        $links = $authorizedPayment['transactions'][0]['related_resources'][0]['authorization']['links'];

        $captureUrl = "";

        foreach ($links as $index => $link) {
            if ($link['rel'] == 'capture') {
                $captureUrl = $link['href'];
            }
        }

        $capture = $this->client->post($captureUrl, [
            'headers' => $this->getHeaders(),
            'json' => [
                'amount' => [
                    'currency' => $authorizedPayment['transactions'][0]['amount']['currency'],
                    'total' => $authorizedPayment['transactions'][0]['amount']['total'],
                ],
                'is_final_capture' => true
            ]
        ]);

//        print_r('====== Capture =======');
//        print_r($capture->json());

        if ($capture->getStatusCode() == 200) {
            return $capture->json();
        } else {
            return false;
        }
    }

    /**
     * Create commission fee which will pay to paxifi
     *
     * @param $payment
     *
     * @return bool|mixed
     */
    public function createPaypalFuturePayment($payment)
    {

        $commission_payment = $this->client->post(\Config::get('paxifi.paypal.url') . 'payments/payment', [
            'headers' => $this->getHeaders(),
            'json' => [
                'intent' => 'authorize',
                'payer' => [
                    'payment_method' => 'paypal'
                ],
                'transactions' => [
                    [
                        "amount" => [
                            "currency" => $this->driver->currency,
                            "total" => $payment['commission']
                        ],
                        "description" => "Paxifi Commission Fee"
                    ]
                ]
            ]
        ]);

//        print_r('===== Authorized Commission ======');
//        print_r($commission_payment->json());

        // if the payment approved, execute the capture payment method.
        if ($commission_payment->getStatusCode(200) && $commission_payment->json()['state'] == 'approved') {
            return $commission_payment->json();
        } else {
            return false;
        }
    }

    /**
     * Verify the paypal single payment
     *
     * @param $payment
     *
     * @return bool|mixed
     */
    public function verifyPaypalSinglePayment($payment)
    {
        $this->getClientAccessToken();

        $res = $this->client->get(\Config::get('paxifi.paypal.url') . 'payments/payment/' . $payment['payment']['proofOfPayment']['response']['id'], [
            'headers' => ['Authorization' => 'Bearer ' . $this->client_access_token],
            'json' => []
        ]);

        if ($res->getStatusCode() == 200) {
            return $this->verified($payment, $res->json()) ? $res->json() : false;
        } else {
            return false;
        }
    }

    /**
     * verify the retrieved payment
     */
    private function verified($paymentPost, $paymentRetrieve) {
        if (
            $paymentPost['payment']['proofOfPayment']['response']['state'] == 'approved' &&
            $paymentPost['payment']['amount'] == $paymentRetrieve['transactions'][0]['amount']['total'] &&
            $paymentPost['payment']['currencyCode'] == $paymentRetrieve['transactions'][0]['amount']['currency'] &&
            $paymentRetrieve['state'] == 'approved' &&
            $paymentRetrieve['transactions'][0]['related_resources'][0]['sale']['state'] == 'completed'
        ) {
            return true;
        }

        return false;
    }

    /**
     * Get paypal access_token via refresh_token.
     *
     * @return mixed
     * @throws \Exception
     */
    private function refreshAccessToken()
    {
        try {
            $refresh_token = $this->driver->paypal_refresh_token;

            $res = $this->client->post(\Config::get('paxifi.paypal.url') . 'oauth2/token', [
                'headers' => $this->getHeaders(),
                'auth' => [\Config::get('paxifi.paypal.client_id'), \Config::get('paxifi.paypal.client_secret')],
                'body' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $refresh_token
                ]
            ]);

            return $res->json()['access_token'];
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Get application access token
     *
     * @throws \Exception
     */
    private function getClientAccessToken()
    {
        try {

            $res = $this->client->post('https://api.sandbox.paypal.com/v1/oauth2/token', [
                'auth' => [\Config::get('paxifi.paypal.client_id'), \Config::get('paxifi.paypal.client_secret')],
                'body' => ['grant_type' => 'client_credentials']
            ]);

            $this->client_access_token = $res->json()['access_token'];

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
} 