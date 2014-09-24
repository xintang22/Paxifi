<?php namespace Paxifi\Subscription\Controller;

use Carbon\Carbon;
use Paxifi\Subscription\Repository\EloquentSubscriptionRepository;
use Paxifi\Subscription\Repository\Validation\CreateSubscriptionValidator;
use Paxifi\Subscription\Transformer\SubscriptionTransformer;
use Paxifi\Support\Controller\ApiController;
use Paxifi\Support\Validation\ValidationException;

class SubscriptionController extends ApiController
{
    public function index($driver = null)
    {
        if (is_null($driver)) {
            $driver = $this->getAuthenticatedDriver();
        }

        if ($driver->subscription) {
            return $this->setStatusCode(200)->respondWithItem($driver->subscription);
        }

        return $this->setStatusCode(200)->respond([]);
    }

    /**
     * Create a driver subscription.
     * Method fired when txn_type is subsrc_signup
     *
     * @param $driver
     * @param $ipn
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store($driver, $ipn)
    {
        try {
            \DB::beginTransaction();

            if ($driver->subscription && $driver->subscription->status != 'past_due') {
                return $driver->subscription;
            }

            $new_subscription = [
                "plan_id" => 1,
                "driver_id" => $driver->id,
                "trial_start" => null,
                "trial_end" => null,
                "start" => Carbon::now(),
                "canceled_at" => null,
                "ended_at" => null,
                "current_period_start" => null,
                "current_period_end" => null,
                "ipn" => $ipn,
                "subscr_id" => $ipn['subscr_id']
            ];

            if ($trail = isset($ipn['period1']) ? explode(" ", $ipn['period1']) : NULL) {

                $new_subscription["trial_start"] = Carbon::now();

                switch ($trail[1]) {
                    case 'M':
                        $new_subscription["trial_end"] = Carbon::now()->addMonths($trail[0]);
                        break;
                    case 'Y':
                        $new_subscription["trial_end"] = Carbon::now()->addYears($trail[0]);
                        break;
                    default:
                        $new_subscription["trial_end"] = Carbon::now()->addDays($trail[0]);
                }
            }

            with(new CreateSubscriptionValidator())->validate($new_subscription);

            if ($subscription = EloquentSubscriptionRepository::create($new_subscription)) {

                $subscription->driver->active();

                \DB::commit();

                return $this->setStatusCode(201)->respondWithItem($subscription);
            }

            return $this->errorWrongArgs('Subscribe failed.');

        } catch (ValidationException $e) {
            return $this->errorWrongArgs($e->getErrors());
        } catch (\Exception $e) {
            return $this->errorInternalError();
        }
    }

    /**
     * Method fired when txn_type is subsrc_payment.
     *
     * @param $driver
     * @param $ipn
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function subscribe($driver, $ipn)
    {

        try {
            \DB::beginTransaction();

            if ($ipn['payment_status'] == 'Completed' && $ipn['business'] == \Config::get('paxifi.paypal.business')) {

                // Update Subscription status to active when the payment was success.
                if ($subscription = EloquentSubscriptionRepository::findSubscriptionBySubscrId($ipn['subscr_id'])->first()) {
                    if ($subscription->driver_id == $ipn['custom'] && $subscription->driver_id == $driver->id) {

                        switch ($recurring = explode(" ", $subscription->ipn['period3'])) {
                            case 'M':
                                $subscription->current_period_end = $subscription->ended_at = Carbon::createFromTimestamp(time($ipn['payment_date']))->addMonths($recurring[0]);
                                break;
                            case 'Y':
                                $subscription->current_period_end = $subscription->ended_at = Carbon::createFromTimestamp(time($ipn['payment_date']))->addYears($recurring[0]);
                                break;
                            default:
                                $subscription->current_period_end = $subscription->ended_at = Carbon::createFromTimestamp(time($ipn['payment_date']))->addDays($recurring[0]);
                        }

                        $subscription->current_period_start = Carbon::now();
                        $subscription->active();

                        // Bind paypal account with driver account.
                        $driver->paypal_account = $ipn['payer_email'];

                        $driver->active();

                        \DB::commit();

                        return $this->setStatusCode(200)->respondWithItem($subscription);
                    }
                }

                return $this->setStatusCode(200)->respond([]);

            }

            return $this->errorWrongArgs('Subscription failed.');
        } catch (\Exception $e) {
            print_r($e->getMessage());
            return $this->errorInternalError();
        }
    }

    /**
     * Handle user subscription cancel.
     *
     * @param $driver
     * @param $ipn
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel($driver, $ipn)
    {
        try {
            \DB::beginTransaction();

            if ($subscription = EloquentSubscriptionRepository::findSubscriptionBySubscrId($ipn['subscr_id'])) {
                if ($subscription->driver_id == $ipn['custom'] ) {
                    // Updated the subscription cancel information.
                    $subscription->canceled_at = Carbon::now();
                    $subscription->cancel_at_period_end = true;
                    $subscription->canceled();

                    \DB::commit();
                }
            }
        } catch (\Exception $e) {
            return $this->errorInternalError();
        }
    }

    /**
     * Subscription end of term.
     *
     * @param $driver
     * @param $ipn
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function eot($driver, $ipn)
    {
        try {
            \DB::beginTransaction();

            if ($subscription = EloquentSubscriptionRepository::findSubscriptionBySubscrId($ipn['subscr_id'])) {

                if ($subscription->driver->id == $driver->id) {

                    // Expired subscription and driver status.
                    $subscription->expired();

                    $subscription->driver->inactive();

                    /*
                     * When the subscription finished, all the commission will get paid to paxifi.
                     *
                     * Commission payment event fired for each driver.
                     */
                    \Event::fire('paxifi.paypal.commission.payment', $driver);

                    \DB::commit();
                }
            }
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
        return new SubscriptionTransformer();
    }
}