<?php namespace Paxifi\Store\Controller;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Paxifi\Paypal\Paypal;
use Paxifi\Store\Repository\Driver\DriverRepository;
use Paxifi\Store\Repository\Driver\Factory\DriverLogoFactory;
use Paxifi\Store\Repository\Driver\Validation\CreateDriverValidator;
use Paxifi\Store\Repository\Driver\Validation\EmailValidation;
use Paxifi\Store\Repository\Driver\Validation\RegisterDriverValidator;
use Paxifi\Store\Repository\Driver\Validation\SettingsValidator;
use Paxifi\Store\Repository\Driver\Validation\UpdateDriverSellerIdValidator;
use Paxifi\Store\Repository\Driver\Validation\UpdateDriverValidator;
use Paxifi\Store\Repository\Driver\Validation\UpdatePasswordValidator;
use Paxifi\Store\Transformer\DriverTransformer;
use Paxifi\Subscription\Repository\EloquentPlanRepository;
use Paxifi\Subscription\Repository\EloquentSubscriptionRepository;
use Paxifi\Support\Controller\ApiController;
use Paxifi\Support\Validation\ValidationException;
use Cache;

class DriverController extends ApiController
{
    /**
     * @var array
     */
    protected $searchables = ['seller_id'];

    /**
     * @var \Paxifi\Store\Repository\Driver\Validation\RegisterDriverValidator
     */
    protected $registerDriverValidator;
    /**
     * @var Paypal
     */
    private $paypal;

    function __construct(RegisterDriverValidator $registerDriverValidator, Paypal $paypal)
    {
        parent::__construct();
        $this->registerDriverValidator = $registerDriverValidator;
        $this->paypal = $paypal;
    }

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
            \DB::beginTransaction();

            with(new CreateDriverValidator())->validate(\Input::except('seller_id', 'status'));

            $driver = DriverRepository::create(\Input::except('seller_id', 'status'));

            \DB::commit();

            return $this->setStatusCode(201)->respondWithItem(DriverRepository::find($driver->id));

        } catch (ValidationException $e) {
            return $this->errorWrongArgs($e->getErrors());
        }

    }

    /**
     * Register a new Driver (a.k.a Store)
     *
     * @return Response
     */
    public function register()
    {
        try {
            \DB::beginTransaction();

            $data = \Input::except('seller_id', 'status');

            // Validate user input + Paypal token
            $this->registerDriverValidator->validate($data);

            // $paypal = \Input::get('paypal');

            // $data['paypal_refresh_token'] = $paypal->refresh_token;

            // create a new driver
            $driver = DriverRepository::create($data);
            // $driver->paypal_metadata_id = $data['metadata_id'];

            // \Event::fire('paxifi.drivers.initialize', [$driver]);

            \Event::fire('paxifi.drivers.registered', [$driver]);

            \DB::commit();

            return $this->setStatusCode(201)->respondWithItem(DriverRepository::find($driver->id));

        } catch (ValidationException $e) {
            return $this->errorWrongArgs($e->getErrors());
        } catch (\InvalidArgumentException $e) {
            return $this->errorWrongArgs($e->getMessage());
        }

    }

    /**
     * @param $driver
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function initialize($driver)
    {
        try {
            \DB::beginTransaction();

            // initiate driver's subscription / trial
            EloquentSubscriptionRepository::initiateTrial(EloquentPlanRepository::firstOrFail(), $driver);

            $accessToken = $this->paypal->getUserAccessToken($driver);

            // Get Driver Paypal information and store the Paypal email
            $info = $this->paypal->getUserInfoByAccessToken($accessToken, $driver);

            $driver->paypal_account = $info->email;
            $driver->status = 1;
            $driver->save();

            \DB::commit();
        } catch (\Exception $e) {
            return $this->errorWrongArgs($e->getMessage());
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

            if (Cache::getDefaultDriver() == "file" || Cache::getDefaultDriver() == "database") {
                $cachedDriver = $driver;
            } else {
                if (is_null(Cache::tags($driver->getTable())->get($driver->id))) {
                    Cache::tags($driver->getTable())->put($driver->id, $driver, 10);
                }

                $cachedDriver = Cache::tags($driver->getTable())->get($driver->id);
            }

            return $this->respondWithItem($cachedDriver);
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
            \DB::beginTransaction();

            if (is_null($driver)) {
                $driver = $this->getAuthenticatedDriver();
            }

            with(new UpdateDriverValidator())->validate(\Input::except('email', 'seller_id', 'status', 'suspended'));

            $driver->update(\Input::except('email', 'seller_id', 'status', 'suspended'));

            \Event::fire('paxifi.store.updated', [$driver]);

            \DB::commit();

            return $this->respondWithItem(DriverRepository::find($driver->id));

        } catch (ValidationException $e) {
            return $this->errorWrongArgs($e->getErrors());
        } catch (\Exception $e) {
            return $this->errorWrongArgs($e->getMessage());
        }
    }

    /**
     * update the seller id
     *
     * @param null $driver
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function seller($driver = NULL)
    {
        try {
            \DB::beginTransaction();

            if (is_null($driver)) {
                $driver = $this->getAuthenticatedDriver();
            }

            $seller_id = \Input::get('seller_id');

            if (!empty($seller_id) && $seller_id == $driver->seller_id) {
                \DB::commit();
            } else {
                with(new UpdateDriverSellerIdValidator())->validate(\Input::only('seller_id'));

                $driver->seller_id = $seller_id;

                $driver->update();

                \DB::commit();
            }

            return $this->respondWithItem($driver);

        } catch (ValidationException $e) {
            return $this->errorWrongArgs($e->getErrors());
        } catch (\Exception $e) {
            return $this->errorInternalError();
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
            \DB::beginTransaction();

            if (is_null($driver)) {
                $driver = $this->getAuthenticatedDriver();
            }

            // logout the driver
            \DB::table('oauth_sessions')
                ->where('oauth_sessions.owner_id', '=', $driver->id)
                ->delete();

            $driver->delete();

            \DB::commit();

            return $this->setStatusCode(204)->respond(array());

        } catch (\Exception $e) {

            return $this->errorInternalError();

        }
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
            \DB::beginTransaction();

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

            \DB::commit();

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

            return $this->errorWrongArgs($e->getErrors());
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

            $result = DriverRepository::search($searchParams)->first();

            if (empty($result->photo)) {
                $result->photo = cloudfront_asset('images/drivers/template/driver_logo.png');
            }

            return $this->respondWithItem($result);

        } catch (ModelNotFoundException $e) {
            return $this->setStatusCode(404)->respondWithError('Store Not Found');
        } catch (\Exception $e) {
            return $this->errorInternalError();
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

        } catch (\RuntimeException $e) {
            return $this->setStatusCode(404)->respondWithError($e->getMessage());
        } catch (\Exception $e) {
            return $this->setStatusCode(500)->respondWithError($e->getMessage());
        }
    }

    /**
     * @param null $driver
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword($driver = null)
    {
        try {
            if (is_null($driver)) {
                $driver = $this->getAuthenticatedDriver();
            }

            if (\Hash::check(\Input::get('origin_password'), $driver->getAuthPassword())) {

                with(new UpdatePasswordValidator())->validate(\Input::all());

                $driver->password = \Input::get('password');

                $driver->save();

                return $this->setStatusCode(200)->respond([
                    'success' => true,
                    'message' => 'Password updated successfully.'
                ]);
            }

            return $this->errorWrongArgs('The password is not correct');

        } catch (ValidationException $e) {
            return $this->errorWrongArgs($e->getErrors());
        } catch (\Exception $e) {
            return $this->errorInternalError();
        }
    }

    /**
     * Driver renew the subscription status when the account expired.
     *
     * @param null $driver
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function renewSubscription($driver = null)
    {
        try {
            \DB::beginTransaction();

            if (is_null($driver)) {
                $driver = $this->getAuthenticatedDriver();
            }

            // Get driver subscription.
            $subscription = $driver->subscription;

            if ($subscription->status != 'past_due') {
                return $this->setStatusCode(406)->respondWithError('Account is still active.');
            }

            $plan = EloquentPlanRepository::findOrFail($subscription->plan_id);

            if ($subscriptionPayment = $this->paypal->subscriptionPayment($plan, $driver)) {

                $driver->paypal_metadata_id = \Input::get('metadata_id');

                $subscription->renewSubscription(EloquentPlanRepository::findOrFail($subscription->plan_id), $driver);

                \DB::commit();

                return $this->respondWithItem($subscription->driver);
            }

            return $this->setStatusCode(406)->respondWithError('Subscription payment failed.');
        } catch (\Exception $e) {
            return $this->errorInternalError($e->getMessage());
        }
    }

    /**
     * Cancel driver subscription.
     *
     * @param $driver
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelSubscription($driver = null)
    {
        try {
            \DB::beginTransaction();

            if (is_null($driver)) {
                $driver = $this->getAuthenticatedDriver();
            }

            $subscription = $driver->subscription;

            // check if subscription is still usable.
            if (in_array($subscription->status, ['canceled', 'past_due'])) {
                return $this->setStatusCode(406)->respondWithError('Account is already canceled subscription or expired.');
            }

            $subscription->canceled();

            \DB::commit();

            return $this->setStatusCode(200)->respondWithItem($driver);
        } catch (\Exception $e) {
            return $this->errorInternalError($e->getMessage());
        }
    }

    /**
     * Reactive subscription if account is canceled.
     *
     * @param null $driver
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function reactiveSubscription($driver = null)
    {
        try {
            \DB::beginTransaction();

            if (is_null($driver)) {
                $driver = $this->getAuthenticatedDriver();
            }

            $subscription = $driver->subscription;

            if ($subscription->status == 'canceled') {

                if (Carbon::now() <= $subscription->trial_end || Carbon::now() <= $subscription->current_period_end) {
                    $subscription->active();

                    \DB::commit();
                    return $this->respondWithItem($driver);
                }

                return $this->setStatusCode(406)->respondWithError('Account is expired, please renew the subscription.');
            }

            return $this->setStatusCode(406)->respondWithError('Account cannot be active.');

        } catch (\Exception $e) {
            return $this->errorInternalError($e->getMessage());
        }
    }

    /**
     * Validate email is available.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function emailValidate()
    {
        try {
            with(new EmailValidation())->validate(\Input::only('email'));

            return $this->setStatusCode(200)->respond(['validate' => true]);
        } catch (ValidationException $e) {
            return $this->errorWrongArgs($e->getErrors());
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
        return new DriverTransformer();
    }
}
