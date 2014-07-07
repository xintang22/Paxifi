<?php namespace Paxifi\Store\Controller;

use Paxifi\Store\Repository\Driver\EloquentDriverRepository;
use Paxifi\Support\Controller\BaseApiController;

class TaxController extends BaseApiController
{
    /**
     * Display a listing of store's defined taxes.
     *
     * @param EloquentDriverRepository $driver
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(EloquentDriverRepository $driver = null)
    {
        if (is_null($driver)) {
            $driver = $this->getAuthenticatedDriver();
        }

        $tax = [
            'enabled' => (boolean)$driver->tax_enabled,
        ];

        if ($driver->tax_enabled) {
            $tax['included_in_price'] = (boolean)$driver->tax_included_in_price;
            $tax['rates'] = $driver->getTaxRates();
        }

        return $this->respond(array(
            'data' => [
                'tax' => $tax,
            ]
        ));
    }
}