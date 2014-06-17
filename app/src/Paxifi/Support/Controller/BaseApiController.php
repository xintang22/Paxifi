<?php namespace Paxifi\Support\Controller;

use Illuminate\Routing\Controller;

class BaseApiController extends Controller
{

    /**
     * @var array An array of response headers
     */
    protected $headers = [];

    /**
     * @var int The response status code
     */
    protected $statusCode = 200;

    /**
     * The Translator implementation.
     *
     * @var \Illuminate\Translation\Translator
     */
    protected $translator;

    function __construct()
    {
        $this->translator = \App::make('translator');

        $this->fireDebugFilters();
    }

    /**
     * Sets the response status code.
     *
     * @param int $statusCode
     *
     * @return $this
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * Retrieves the response status code.
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Sets the response headers.
     *
     * @param array $headers
     *
     * @return $this
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Retrieves the response headers.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Generates the error response payload.
     *
     * @param string $message
     *
     * @param null|string $context
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithError($message, $context = null)
    {
        return $this->respond(array(
            'error' => array(
                'context' => $context,
                'message' => $message,
                'code' => $this->getStatusCode(),
            )
        ));
    }

    /**
     * Generates the JSON Response.
     *
     * @param $data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respond($data)
    {
        return \Response::json($data, $this->statusCode, $this->headers);
    }

    /**
     * Generates a Response with a 403 HTTP header and a given message.
     *
     * @param string $message
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function errorForbidden($message = 'Forbidden')
    {
        return $this->setStatusCode(403)->respondWithError($message);
    }

    /**
     * Generates a Response with a 500 HTTP header and a given message.
     *
     * @param string $message
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function errorInternalError($message = 'Internal Error')
    {
        return $this->setStatusCode(500)->respondWithError($message);
    }

    /**
     * Generates a Response with a 404 HTTP header and a given message.
     *
     * @param string $message
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function errorNotFound($message = 'Resource Not Found')
    {
        return $this->setStatusCode(404)->respondWithError($message);
    }

    /**
     * Generates a Response with a 401 HTTP header and a given message.
     *
     * @param string $message
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function errorUnauthorized($message = 'Unauthorized')
    {
        return $this->setStatusCode(401)->respondWithError($message);
    }

    /**
     * Generates a Response with a 400 HTTP header and a given message.
     *
     * @param string $message
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function errorWrongArgs($message = 'Wrong Arguments')
    {
        return $this->setStatusCode(400)->respondWithError($message);
    }

    /**
     * Fires clockwork events
     */
    private function fireDebugFilters()
    {
        $this->beforeFilter(function () {
            \Event::fire('clockwork.controller.start');
        });

        $this->afterFilter(function () {
            \Event::fire('clockwork.controller.end');
        });
    }

} 