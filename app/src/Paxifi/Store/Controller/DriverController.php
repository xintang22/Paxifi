<?php namespace Paxifi\Store\Controller;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Paxifi\Store\Repository\Driver\DriverRepository;
use Paxifi\Store\Transformer\DriverTransformer;
use Paxifi\Support\Controller\ApiController;

class DriverController extends ApiController
{
    protected $searchables = ['seller_id'];

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

    /**
     * Search store by different criteria.
     *
     * @TODO: add more searchable fields
     */
    public function search()
    {
        try {
            $q = \Input::get('q');

            /** @var \Illuminate\Support\Collection $searchParams */
            $searchParams = $this->extractSearchParams(explode(',', $q));

            if ($searchParams->isEmpty()) {
                return $this->errorWrongArgs('Missing or invalid search arguments.');
            }

            $results = DriverRepository::search($searchParams);

            return $this->respond(array(
                'success' => true,
                'count' => $results->count(),
                'results' => $results->toArray(),
            ));

        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound('Store not found');
        }

    }

    /**
     * Extract the search properties and their values.
     *
     * @param $segments
     *
     * @return \Illuminate\Support\Collection
     */
    private function extractSearchParams($segments)
    {
        $params = new Collection();

        foreach ($segments as $segment) {

            list($column, $value) = explode('=', $segment);

            if (in_array($column, $this->searchables)) {

                $params->push(array(
                    'column' => $column,
                    'operator' => '=',
                    'value' => $value,
                ));
            }
        }

        return $params;
    }


}
