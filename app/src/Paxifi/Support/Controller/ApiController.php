<?php namespace Paxifi\Support\Controller;

/**
 * The Resource Api Controller
 * @package Paxifi\Support\Controller
 */
abstract class ApiController extends BaseApiController
{
    /**
     * @var \Paxifi\Support\Response\Response
     */
    protected $response;

    /**
     * @var bool
     */
    protected $paginationEnabled;

    /**
     * @var int
     */
    protected $perPage;

    /**
     * The constructor.
     */
    function __construct()
    {
        $this->response = \App::make('Paxifi\Support\Response\Response');

        $this->response->setRequestedScopes(explode(',', \Input::get('embed')));

        $this->perPage = \Input::get('count', \Config::get('paxifi.api.pagination.count.default'));

        $this->paginationEnabled = \Config::get('paxifi.api.pagination.enabled');

        parent::__construct();
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
}