<?php namespace Paxifi\Subscription\Controller;

use Carbon\Carbon;
use Paxifi\Store\Repository\Driver\EloquentDriverRepository;
use Paxifi\Subscription\Repository\EloquentSubscriptionRepository;
use Paxifi\Subscription\Repository\Validation\CreateSubscriptionValidator;
use Paxifi\Subscription\Transformer\SubscriptionTransformer;
use Paxifi\Support\Controller\ApiController;
use Paxifi\Support\Validation\ValidationException;

class SubscriptionController extends ApiController
{

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
                "txn_type" => $ipn['txn_type'],
                "payer_id" => $ipn['payer_id'],
                "ipn_track_id" => $ipn['ipn_track_id']
            ];

            if($trail = isset($ipn['period1']) ? explode(" ", $ipn['period1']) : NULL) {

                $new_subscription["trial_start"] = $new_subscription["current_period_start"] = Carbon::now();

                switch($trail[1]) {
                    case 'M':
                        $new_subscription["trial_end"] = Carbon::now()->addMonths($trail[0]);
                        $new_subscription["current_period_end"] = Carbon::now()->addMonths($trail[0]);
                        break;
                    case 'Y':
                        $new_subscription["trial_end"] = Carbon::now()->addYears($trail[0]);
                        $new_subscription["current_period_end"] = Carbon::now()->addYears($trail[0]);
                        break;
                    default:
                        $new_subscription["trial_end"] = Carbon::now()->addDays($trail[0]);
                        $new_subscription["current_period_end"] = Carbon::now()->addDays($trail[0]);
                }
            }

            $recurring = explode(" ", $ipn['period3']);

            switch($recurring[1]) {
                case 'M':
                    $new_subscription["end"] = Carbon::now()->addMonths($recurring[0]);
                    $new_subscription["current_period_end"] = Carbon::now()->addMonths($recurring[0]);
                    break;
                case 'Y':
                    $new_subscription["end"] = Carbon::now()->addYears($recurring[0]);
                    $new_subscription["current_period_end"] = Carbon::now()->addYears($recurring[0]);
                    break;
                default:
                    $new_subscription["end"] = Carbon::now()->addDays($recurring[0]);
                    $new_subscription["current_period_end"] = Carbon::now()->addDays($recurring[0]);
            }

            with(new CreateSubscriptionValidator())->validate($new_subscription);

            if ($subscription = EloquentSubscriptionRepository::create($new_subscription)) {

                \DB::commit();

                return $this->setStatusCode(201)->respondWithItem($subscription);
            }

            return $this->errorWrongArgs('Subscribe failed.');

        } catch (ValidationException $e) {
            return $this->errorWrongArgs($e->getErrors());
        } catch(\Exception $e) {
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

                if ($subscription = EloquentSubscriptionRepository::findSubscriptionByIpnTrackId($ipn['ipn_track_id'])->first()) {
                    if ($subscription->driver_id == $ipn['custom'] && $subscription->driver_id == $driver->id) {
                        $subscription->status = "active";
                        $subscription->current_period_start = Carbon::now();
                        $subscription->save();

                        // Update driver status.
                        $driver->paypal_account = $ipn['payer_email'];

                        $driver->active();

                        \DB::commit();

                        return $this->setStatusCode(200)->respond($driver);
                    }
                }

                return $this->setStatusCode(200)->respond($driver);

            }

            return $this->setStatusCode(400)->respondWithError('Subscription failed.');
        } catch (\Exception $e) {
            return $this->errorInternalError();
        }

//        \Log::useFiles(storage_path() . '/logs/sub-' . time() . '.txt');
//
//        \Log::info($subscribe);
    }

    public function cancel($driver, $ipn)
    {
        try {
            \DB::beginTransaction();

            if ($subscription = EloquentSubscriptionRepository::findSubscriptionByIpnTrackId($ipn['ipn_track_id'])->first()) {
                $subscription->canceled_at = Carbon::now();
                $subscription->save();
            }

            \DB::commit();
        } catch (\Exception $e) {

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