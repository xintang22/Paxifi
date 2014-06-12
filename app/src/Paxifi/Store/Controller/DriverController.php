<?php namespace Paxifi\Store\Controller;

use Paxifi\Store\Repository\Driver\DriverRepository;
use Paxifi\Store\Transformer\DriverTransformer;
use Paxifi\Support\Controller\ApiController;

class DriverController extends ApiController
{
    /**
     * Display a listing of drivers.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->respondWithCollection(DriverRepository::all());
    }

    /**
     * Store a newly created driver
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store()
    {
        $data = \Input::all();

        if ($driver = DriverRepository::create($data)) {
            return $this->setStatusCode(201)->respondWithItem($driver);
        }

        return $this->errorWrongArgs(DriverRepository::getValidationErrors());
    }

    /**
     * Display the specified driver.
     *
     * @param  \Paxifi\Store\Repository\Driver\DriverRepository $driver
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($driver)
    {
        return "Show store {$driver->id}";
    }

    /**
     * Update the specified driver in storage.
     *
     * @param  \Paxifi\Store\Repository\Driver\DriverRepository $driver
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($driver)
    {
        return "Update store {$driver->id}";
    }

    /**
     * Remove the specified driver from storage.
     *
     * @param  \Paxifi\Store\Repository\Driver\DriverRepository $driver
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($driver)
    {
        return "Delete store {$driver->id}";
    }

    /**
     * Retrieves the Data Transformer
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function getTransformer()
    {
        return new DriverTransformer();
    }

    /**
     * Checks if the seller id is available.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkSellerId()
    {
        if ($sellerId = \Input::get('id')) {
            $driver = DriverRepository::findBySellerId($sellerId);

            if (!$driver->count()) {
                return $this->respond(array(
                    'success' => true,
                    'message' => $this->translator->trans('responses.store.seller_id_available', array('seller_id' => $sellerId))
                ));
            }

            return $this->errorWrongArgs(
                $this->translator->trans('responses.store.seller_id_not_available', array('seller_id' => $sellerId))
            );
        }

        return $this->errorWrongArgs($this->translator->trans('responses.store.missing_seller_id'));

    }

    /**
     * Retrieves the stores sales
     *
     * @param  \Paxifi\Store\Repository\Driver\DriverRepository $driver
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sales($driver)
    {
        return "Show store {$driver->id}'s sales";
    }

}
