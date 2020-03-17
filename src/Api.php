<?php namespace Signedpay\API;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class Api
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    private $merchantId;

    /**
     * @var string
     */
    private $privateKey;

    /**
     * @var \Exception
     */
    private $exception;

    public function __construct(
        $merchantId,
        $privateKey,
        $baseUri = 'https://pay.signedpay.com/api/v1/'
    ) {
        $this->merchantId = $merchantId;
        $this->privateKey = $privateKey;

        $this->client = new Client(
            [
                'base_uri' => $baseUri,
                'verify'   => true,
            ]
        );
    }

    /**
     * @param array $attributes
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function charge(array $attributes)
    {
        return $this->sendRequest('charge', $attributes);
    }

    /**
     * @param array $attributes
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function recurring(array $attributes)
    {
        return $this->sendRequest('recurring', $attributes);
    }

    /**
     * @param array $attributes
     *
     * @return mixed
     */
    public function status(array $attributes)
    {
        return $this->sendRequest('status', $attributes);
    }

    /**
     * @param array $attributes
     *
     * @return mixed
     */
    public function refund(array $attributes)
    {
        return $this->sendRequest('refund', $attributes);
    }

    /**
     * @param array $attributes
     *
     * @return mixed
     */
    public function initPayment(array $attributes)
    {
        return $this->sendRequest('init-payment', $attributes);
    }

    /**
     * @return string
     */
    public function getMerchantId()
    {
        return $this->merchantId;
    }

    /**
     * @return string
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    /**
     * @param $method
     * @param $attributes
     *
     * @return mixed
     */
    public function sendRequest($method, $attributes)
    {
        $request = $this->makeRequest($method, $attributes);

        try {
            $response = $this->client->send($request);

            $responseBody = $response->getBody()->getContents();

            return json_decode($responseBody, true);

        } catch (\Exception $e) {

            $this->exception = $e;

            return [
                'status' => 'error',
                'error'  => [
                    'code'           => '0.00',
                    'exception_code' => $e->getCode(),
                    'messages'       => [
                        'Unexpected error',
                        'Caught ' . get_class($e) . ' exception.',
                        $e->getMessage(),
                    ],
                ],
            ];
        }
    }

    /**
     * @return \Exception
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @param string $path
     * @param array  $attributes
     *
     * @return Request
     */
    private function makeRequest($path, array $attributes)
    {
        $data = json_encode($attributes);

        return new Request(
            'POST', $path, [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
            'Merchant'     => $this->getMerchantId(),
            'Signature'    => $this->generateSignature($data),
        ], $data
        );
    }

    /**
     * @param $data
     *
     * @return string
     */
    private function generateSignature($data)
    {
        return base64_encode(
            hash_hmac('sha512',
                $this->getMerchantId() . $data . $this->getMerchantId(),
                $this->getPrivateKey())
        );
    }
}
