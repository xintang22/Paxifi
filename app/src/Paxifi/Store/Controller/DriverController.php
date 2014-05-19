<?php namespace Paxifi\Store\Controller;

use Paxifi\Store\Repository\Driver\DriverRepositoryInterface;
use Paxifi\Store\Transformer\DriverTransformer;
use Paxifi\Support\Controller\ApiController;

class DriverController extends ApiController
{
    /**
     * @var \Paxifi\Store\Repository\Driver\DriverRepositoryInterface
     */
    private $driver;

    /**
     * @var \Paxifi\Store\Transformer\DriverTransformer
     */
    private $transformer;

    function __construct(DriverRepositoryInterface $driver, DriverTransformer $transformer)
    {
        $this->driver = $driver;
        $this->transformer = $transformer;

        parent::__construct();
    }

    /**
     * Display a listing of drivers.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->respondWithCollection($this->driver->all());
    }

    /**
     * Store a newly created driver
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store()
    {
        $data = \Input::all();

        if ($driver = $this->driver->create($data)) {
            return $this->setStatusCode(201)->respondWithItem($driver);
        }

        return $this->errorWrongArgs($this->driver->getValidationErrors());
    }

    /**
     * Display the specified driver.
     *
     * @param  int|string $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified driver in storage.
     *
     * @param  int|string $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id)
    {
        //
    }

    /**
     * Remove the specified driver from storage.
     *
     * @param  int|string $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Retrieves the Data Transformer
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function getTransformer()
    {
        return $this->transformer;
    }

    /**
     * Checks if the seller id is available.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkSellerId()
    {
        if ($sellerId = \Input::get('id')) {
            $driver = $this->driver->findBySellerId($sellerId);

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

}
