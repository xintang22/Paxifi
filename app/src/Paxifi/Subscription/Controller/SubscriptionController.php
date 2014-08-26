<?php namespace Paxifi\Subscription\Controller;

use Carbon\Carbon;
use Paxifi\Store\Repository\Driver\EloquentDriverRepository;
use Paxifi\Subscription\Repository\EloquentSubscriptionRepository;
use Paxifi\Subscription\Transformer\SubscriptionTransformer;
use Paxifi\Support\Controller\ApiController;
use Paxifi\Support\Validation\ValidationException;

class SubscriptionController extends ApiController
{

    /**
     * Create a driver subscription.
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

            $trail = explode(" ", $ipn['period1']);
            print_r($trail);
            die;
            $new_subscription = [
                "plan_id" => 1,
                "driver_id" => $driver->id,
                "trial_start" => Carbon::createFromTimestamp(Carbon::now()->setTimezone(\Config::get('app.timezone'))->format('U'))
            ];

            switch($trail[1]) {
                case 'D':
                    $current_time = Carbon::now()->setTimezone(\Config::get('app.timezone'))->format('U');
                    $new_subscription["trial_end"] = Carbon::createFromTimestamp(($current_time + $trail[0] * 24 * 3600));
                    break;
                case 'M':
                    $new_subscription["trial_end"] = Carbon::createFromTimestamp((Carbon::now()->setTimezone(\Config::get('app.timezone'))->format('U')) + $trail[0] * 24*60*60);
                default: ;
            }

            if ($subscription = EloquentSubscriptionRepository::create(\Input::all())) {

            }

            \DB::commit();
        } catch (ValidationException $e) {

        } catch(\Exception $e) {
            return $this->errorInternalError();
        }
    }

    public function subscribe()
    {



//        \Log::useFiles(storage_path() . '/logs/sub-' . time() . '.txt');
//
//        \Log::info($subscribe);
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