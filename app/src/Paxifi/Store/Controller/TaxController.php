<?php namespace Paxifi\Store\Controller;

use Paxifi\Store\Repository\Driver\DriverRepository;
use Paxifi\Store\Repository\Driver\EloquentDriverRepository;
use Paxifi\Support\Controller\ApiController;
use Paxifi\Tax\transformer\TaxRateTransformer;

class TaxController extends ApiController
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
        $taxes = $driver->getTaxRates();
        return $this->respondWithCollection($taxes);
    }

    /**
     * Display a listing of store's defined taxes.
     *
     * @param EloquentDriverRepository $driver
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(EloquentDriverRepository $driver)
    {
        if (in_array($driver->getCountry(), ['US', 'UK'])) {
            return $this->errorWrongArgs('Store cannot have custom global tax rates');
        }

        if ($driver->customTaxRates()->get()->count() == 1) {
            return $this->errorWrongArgs('Store cannot have more than one global tax rate.');
        }

        try {
            $driver->customTaxRates()->create([
                'category' => 'global',
                'amount' => \Input::get('amount'),
                'included_in_price' => (boolean)\Input::get('included_in_price', true),
            ]);

            return $this->respondWithCollection(DriverRepository::find($driver->id)->getTaxRates());

        } catch (\Exception $e) {
            return $this->errorInternalError($e->getMessage());
        }
    }

    /**
     * Retrieves the Data Transformer
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function getTransformer()
    {
        return new TaxRateTransformer();
    }
}