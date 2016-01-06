<?php


namespace SellsyApi\Request;


use GuzzleHttp\Client;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;
use SellsyApi\Exception\OAuthException;

class Request implements AsyncRequestInterface {

    /**
     * @var string
     */
    protected $endPoint;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $userToken;
    /**
     * @var string
     */
    protected $userSecret;
    /**
     * @var string
     */
    protected $consumerToken;
    /**
     * @var string
     */
    protected $consumerSecret;

    /**
     * Request constructor.
     *
     * @param string $userToken
     * @param string $userSecret
     * @param string $consumerToken
     * @param string $consumerSecret
     * @param string $endPoint
     */
    public function __construct ($userToken, $userSecret, $consumerToken, $consumerSecret, $endPoint = NULL) {
        $this->userToken      = $userToken;
        $this->userSecret     = $userSecret;
        $this->consumerToken  = $consumerToken;
        $this->consumerSecret = $consumerSecret;
        if (!is_null($endPoint)) {
            $this->endPoint = $endPoint;
        }
    }

    /**
     * @return Client
     */
    protected function getClient () {
        if (is_null($this->client)) {
            if (is_null($this->getEndPoint())) {
                throw new \RuntimeException('endPoint is not set');
            }
            $this->client = new Client(['base_uri' => $this->endPoint]);
        }
        return $this->client;
    }

    /**
     * @return string
     */
    public function getEndPoint () {
        return $this->endPoint;
    }

    /**
     * @param string $endPoint
     *
     * @return Request
     */
    public function setEndPoint ($endPoint) {
        $this->endPoint = $endPoint;
        return $this;
    }


    /**
     * @param string $method
     * @param mixed  $params
     *
     * @return PromiseInterface
     */
    public function callAsync ($method, $params) {
        $requestId = uniqid();
        $options   = $this->prepareCall($method, $params, $requestId);
        $response  = $this->getClient()->postAsync('', $options);

        $promise = new Promise(function ($unwrap) use ($response) {
            return $response->wait($unwrap);
        }, function () use ($response) {
            return $response->cancel();
        });

        $response->then(function (ResponseInterface $res) use ($promise) {
            try {
                $promise->resolve($this->handleResponse($res->getBody()));
                return $res;
            } catch (\Exception $e) {
                $promise->reject($e);
                throw $e;
            }
        }, function (\Exception $reqException) use ($promise, $requestId) {
            $this->logResponse($requestId, strval($reqException));
            $promise->reject($reqException);
            return $reqException;
        });

        $promise->then(function ($res) use ($requestId) {
            $this->logResponse($requestId, json_encode($res));
            return $res;
        }, function ($res) use ($requestId) {
            $this->logResponse($requestId, strval($res));
            return $res;
        });

        return $promise;

    }

    /**
     * @param mixed $method
     * @param mixed $params
     *
     * @return mixed
     */
    public function call ($method, $params) {
        $requestId = uniqid();
        $options   = $this->prepareCall($method, $params, $requestId);

        $result = $this->handleResponse($this->getClient()->post('', $options)->getBody());
        $this->logResponse($requestId, json_encode($result));
        return $result;
    }

    /**
     * TODO
     *
     * @param string $requestId
     * @param string $message
     */
    protected function logRequest ($requestId, $message) {
        printf("[%s]%s --> %s\n", $requestId, date('c'), $message);
    }

    /**
     * TODO
     *
     * @param string $requestId
     * @param string $message
     */
    protected function logResponse ($requestId, $message) {
        printf("[%s]%s <-- %s\n", $requestId, date('c'), $message);
    }

    /**
     * @param string $requestId
     *
     * @return \string[]
     */
    protected function getOAuthHeader (&$requestId) {
        $encodedKey  = rawurlencode($this->consumerSecret) . '&' . rawurlencode($this->userSecret);
        $oauthParams = ['oauth_consumer_key'     => $this->consumerToken,
                        'oauth_token'            => $this->userToken,
                        'oauth_nonce'            => $requestId,
                        'oauth_timestamp'        => time(),
                        'oauth_signature_method' => 'PLAINTEXT',
                        'oauth_version'          => '1.0',
                        'oauth_signature'        => $encodedKey,
        ];


        $values = [];
        foreach ($oauthParams as $key => $value) {
            $values[] = sprintf('%s="%s"', $key, rawurlencode($value));
        }

        return ['Authorization' => 'OAuth ' . implode(', ', $values), 'Expect' => ''];
    }

    /**
     * @param mixed $response
     *
     * @return mixed
     * @throws \SellsyApi\Exception\OAuthException
     * @throws \SellsyApi\Exception\SellsyError
     * @throws \UnexpectedValueException
     */
    protected function handleResponse ($response) {

        if (strstr($response, 'oauth_problem')) {
            throw new OAuthException($response);
        }

        $array = json_decode($response, TRUE);

        if (!is_array($array)) {
            throw  new \UnexpectedValueException(sprintf('Unable to decode JSON Sellsy\'s response (%s)', $response));
        }

        if (!array_key_exists('status', $array)) {
            throw new \UnexpectedValueException(sprintf('Field status not found in Sellsy\'s response (%s)',
                                                        $response));
        }

        if ($array['status'] != 'success') {
            if (array_key_exists('error', $array) && is_array($error = $array['error'])
                && array_key_exists('code', $error)
            ) {
                throw new \SellsyApi\Exception\SellsyError(array_key_exists('message', $error) ? $error['message'] : '',
                                                           $error['code'],
                                                           array_key_exists('more', $error) ? $error['more'] : NULL);
            } else {
                throw new \UnexpectedValueException('Unknown Sellsy error');
            }
        }

        if (!array_key_exists('response', $array)) {
            throw new \UnexpectedValueException(sprintf('Field response not found in Sellsy\'s response (%s)',
                                                        $response));
        }

        return $array['response'];

    }

    /**
     * @param string $method
     * @param mixed  $params
     * @param string $requestId
     *
     * @return array
     */
    protected function prepareCall (&$method, &$params, &$requestId) {
        $doIn = ['method' => $method, 'params' => $params];

        $postFields = ['request' => '1', 'io_mode' => 'json', 'do_in' => json_encode($doIn)];
        $multipart  = [];
        foreach ($postFields as $key => $value) {
            $multipart[] = ['name' => $key, 'contents' => $value];
        }

        $options = ['headers'   => $this->getOAuthHeader($requestId),
                    'multipart' => $multipart,
                    'verify'    => !preg_match("!^https!i", $this->endPoint),
        ];

        $this->logRequest($requestId, json_encode($doIn));

        return $options;
    }


}
