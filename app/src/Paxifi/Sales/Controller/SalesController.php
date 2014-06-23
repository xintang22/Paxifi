<?php namespace Paxifi\Sales\Controller;

use Paxifi\Sales\Transformer\SaleTransformer;
use Paxifi\Store\Repository\Driver\EloquentDriverRepository;
use Paxifi\Support\Controller\ApiController;

class SalesController extends ApiController
{

    /**
     * Display a listing of all sales.
     *
     * @param \Paxifi\Store\Repository\Driver\EloquentDriverRepository $driver
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(EloquentDriverRepository $driver = null)
    {

        return $this->respondWithCollection($driver->sales());

    }


    /**
     * Retrieves the Data Transformer
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function getTransformer()
    {
        return new SaleTransformer();
    }
}