<?php namespace Paxifi\Store\Controller;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Paxifi\Store\Repository\Driver\DriverRepository;
use Paxifi\Store\Repository\Driver\Factory\DriverLogoFactory;
use Paxifi\Store\Repository\Driver\Validation\CreateDriverValidator;
use Paxifi\Store\Repository\Driver\Validation\SettingsValidator;
use Paxifi\Store\Repository\Driver\Validation\UpdateDriverValidator;
use Paxifi\Store\Transformer\DriverTransformer;
use Paxifi\Support\Controller\ApiController;
use Paxifi\Support\Validation\ValidationException;

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
        try {

            with(new CreateDriverValidator())->validate(\Input::all());

            $driver = DriverRepository::create(\Input::all());

            \Event::fire('paxifi.store.created', [$driver]);

            return $this->setStatusCode(201)->respondWithItem(DriverRepository::find($driver->id));

        } catch (ValidationException $e) {

            return $this->errorWrongArgs($e->getErrors()->all());

        }

    }

    /**
     * Display the specified driver.
     *
     * @param  \Paxifi\Store\Repository\Driver\DriverRepository $driver
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($driver = null)
    {
        try {
            if (is_null($driver)) {
                $driver = $this->getAuthenticatedDriver();
            }

            return $this->respondWithItem($driver);
        } catch (\Exception $e) {
            $this->errorInternalError();
        }

    }

    /**
     * Update the specified driver in storage.
     *
     * @param  \Paxifi\Store\Repository\Driver\DriverRepository $driver
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($driver = null)
    {
        try {

            if (is_null($driver)) {
                $driver = $this->getAuthenticatedDriver();
            }

            with(new UpdateDriverValidator())->validate(\Input::except('email', 'seller_id'));

            $driver->update(\Input::all());

            \Event::fire('paxifi.store.updated', [$driver]);

            return $this->respondWithItem(DriverRepository::find($driver->id));

        } catch (ValidationException $e) {

            return $this->errorWrongArgs($e->getErrors()->all());
        }
    }

    /**
     * Remove the specified driver from storage.
     *
     * @param  \Paxifi\Store\Repository\Driver\DriverRepository $driver
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($driver = null)
    {
        try {

            if (is_null($driver)) {
                $driver = $this->getAuthenticatedDriver();
            }

            // logout the driver
            DB::table('oauth_sessions')
                ->where('oauth_sessions.owner_id', '=', $driver->id)
                ->delete();

            $driver->delete();

            return $this->setStatusCode(204)->respond(array());

        } catch (\Exception $e) {

            return $this->errorInternalError();

        }
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
     * Retrieves the stores settings
     *
     * @param  \Paxifi\Store\Repository\Driver\DriverRepository $driver
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function settings($driver = null)
    {
        if (is_null($driver)) {
            $driver = $this->getAuthenticatedDriver();
        }

        return $this->respond(array(
            'data' => array(
                'settings' => array(
                    'notify_sale' => (boolean)$driver->notify_sale,
                    'notify_inventory' => (boolean)$driver->notify_inventory,
                    'notify_feedback' => (boolean)$driver->notify_feedback,
                    'notify_billing' => (boolean)$driver->notify_billing,
                    'notify_others' => (boolean)$driver->notify_others,
                ),
            ),
        ));
    }

    /**
     * Updates the stores settings
     *
     * @param  \Paxifi\Store\Repository\Driver\DriverRepository $driver
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSettings($driver = null)
    {
        try {

            if (is_null($driver)) {
                $driver = $this->getAuthenticatedDriver();
            }

            with(new SettingsValidator())->validate(\Input::all());

            $driver->notify_sale = \Input::get('notify_sale', $driver->notify_sale);
            $driver->notify_inventory = \Input::get('notify_inventory', $driver->notify_inventory);
            $driver->notify_feedback = \Input::get('notify_feedback', $driver->notify_feedback);
            $driver->notify_billing = \Input::get('notify_billing', $driver->notify_billing);
            $driver->notify_others = \Input::get('notify_others', $driver->notify_others);

            $driver->save();

            \Event::fire('paxifi.store.settings.updated', [$driver]);

            return $this->respond(array(
                'data' => array(
                    'settings' => array(
                        'notify_sale' => (boolean)$driver->notify_sale,
                        'notify_inventory' => (boolean)$driver->notify_inventory,
                        'notify_feedback' => (boolean)$driver->notify_feedback,
                        'notify_billing' => (boolean)$driver->notify_billing,
                        'notify_others' => (boolean)$driver->notify_others,
                    ),
                ),
            ));

        } catch (ValidationException $e) {

            return $this->errorWrongArgs($e->getErrors()->all());
        }

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

            $results = DriverRepository::search($searchParams)->toArray();

            if (empty($results[0]['photo'])) {
                $results[0]['photo'] = url('images/drivers/template/driver_logo.png');
            }

            return $this->respond(array(
                'success' => true,
                'count' => count($results),
                'results' => $results,
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

    /**
     * Route to generate driver logo
     *
     * @param $driver
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logo($driver = null)
    {
        try {
            if (is_null($driver)) {
                $driver = $this->getAuthenticatedDriver();
            }

            $factory = new DriverLogoFactory();

            $factory->setDriver($driver);

            $response = $factory->buildDriverLogo();

            return $this->setStatusCode(200)->respond($response);

        } catch(\RuntimeException $e)
        {
            return $this->setStatusCode(404)->respondWithError($e->getMessage());
        } catch(\Exception $e) {
            return $this->setStatusCode(500)->respondWithError($e->getMessage());
        }

    }
}
