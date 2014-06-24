<?php namespace Paxifi\Sales\Controller;

use Carbon\Carbon;
use Paxifi\Sales\Repository\SaleCollection;
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
        $from = ($from = (int) \Input::get('from')) ? Carbon::createFromTimestamp($from) : $driver->created_at;
        $to = ($to = (int) \Input::get('to')) ? Carbon::createFromTimestamp($to) : Carbon::now();

        $sales = new SaleCollection($driver->sales($from, $to));

        return $this->respond($sales->toArray());
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