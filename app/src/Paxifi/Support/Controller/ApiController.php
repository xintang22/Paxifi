<?php namespace Paxifi\Support\Controller;

use Illuminate\Routing\Controller;
use Paxifi\Support\Response\Response;

/**
 * The Api Base Controller
 * @package Paxifi\Support\Controller
 */
abstract class ApiController extends Controller
{
    /**
     * @var \Paxifi\Support\Response\Response
     */
    protected $response;

    /**
     * @var array An array of response headers
     */
    protected $headers = [];

    /**
     * @var int The response status code
     */
    protected $statusCode = 200;

    /**
     * @var bool
     */
    protected $paginationEnabled;

    /**
     * @var int
     */
    protected $perPage;

    /**
     * The Translator implementation.
     *
     * @var \Illuminate\Translation\Translator
     */
    protected $translator;

    /**
     * The constructor.
     */
    function __construct()
    {
        $this->response = \App::make('Paxifi\Support\Response\Response');

        $this->response->setRequestedScopes(explode(',', \Input::get('embed')));

        $this->translator = \App::make('translator');

        $this->perPage = \Input::get('count', \Config::get('paxifi.api.pagination.count.default'));

        $this->paginationEnabled = \Config::get('paxifi.api.pagination.enabled');

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
     * Retrieves the Data Transformer
     *
     * @return \League\Fractal\TransformerAbstract
     */
    abstract public function getTransformer();


    /**
     * Responds with single resource.
     *
     * @param mixed $data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function respondWithItem($data)
    {
        $this->response
            ->setTransformer($this->getTransformer())
            ->setContent($data);

        return $this->respond($this->response->withItem());
    }

    /**
     * Responds with collection.
     *
     * @param mixed $data
     * @param bool $paginated
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function respondWithCollection($data, $paginated = false)
    {
        $this->response
            ->setTransformer($this->getTransformer())
            ->setContent($data);

        return $this->respond($this->response->withCollection($paginated));
    }

    /**
     * Generates the error response payload.
     *
     * @param string $message
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithError($message)
    {
        return $this->respond(array(
            'error' => true,
            'message' => $message
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