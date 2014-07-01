<?php namespace Paxifi\Sales\Controller;

use Carbon\Carbon;
use Paxifi\Sales\Repository\SaleCollection;
use Paxifi\Store\Repository\Driver\EloquentDriverRepository;
use Paxifi\Support\Controller\BaseApiController;

class SalesController extends BaseApiController
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
        $from = ($from = (int)\Input::get('from')) ? Carbon::createFromTimestamp($from) : $driver->created_at;
        $to = ($to = (int)\Input::get('to')) ? Carbon::createFromTimestamp($to) : Carbon::now();

        $sales = new SaleCollection($driver->sales($from, $to));

        return $this->respond($sales->toArray());
    }

    /**
     * Display the sales forecasts.
     *
     * @param \Paxifi\Store\Repository\Driver\EloquentDriverRepository $driver
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function forecasts(EloquentDriverRepository $driver)
    {
        $totalItems = 0;
        $totalSales = 0;

        $driver->products()->get()->each(function ($product) use (&$totalItems, &$totalSales) {
            $totalItems += $product->inventory;
            $totalSales += $product->unit_price * $product->inventory;
        });

        $commissionRate = \Config::get('paxifi.commission.rate', 0.05);
        $totalCommission = $commissionRate * $totalSales;

        $totalProfit = $totalSales - $totalCommission;

        return $this->respond(array(
            'forecasts' => array(
                'sales' => $totalSales,
                'profit' => $totalProfit,
                'commission' => $totalCommission,
                'items' => $totalItems,
            ),
            'date' => (string)Carbon::now(),
        ));
    }
}