<?php

use Illuminate\Console\Command;
use Paxifi\Commission\Repository\EloquentCommissionRepository;
use Paxifi\Sales\Repository\SaleCollection;
use Paxifi\Store\Repository\Driver\EloquentDriverRepository;
use Paxifi\Subscription\Repository\EloquentPlanRepository;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class CrontabCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'paxifi:crontab';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Cron job to subscribe and pay commissions.';

    /**
     * @var
     */
    protected $paypal;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

        $this->paypal = App::make('Paxifi\Paypal\Paypal');
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
        try {
            /**
             * This command will be run by crontab every night,
             * and check the all the drivers if the subscription ended.
             */

            $drivers = EloquentDriverRepository::where('status', '=', 1)->get();

            foreach($drivers as $key => $driver) {
                $subscription = $driver->subscription;

                /**
                 * Need charge commission fee.
                 */
                if ($subscription->needChargeCommission() && ! $driver->paidCommission($subscription->current_period_end)) {

                    $this->payCommission($subscription, $driver);

                }

                /**
                 * Need change subscription.
                 */
                if ($subscription->needChargeSubscription()) {
                    $plan = EloquentPlanRepository::findOrFail($subscription->plan_id);

                    if ($subscriptionPayment = $this->paypal->subscriptionPayment($plan, $driver)) {

                        $subscription->subscribe(EloquentPlanRepository::findOrFail($subscription->plan_id));

                        // Todo:: record all successful subscription payment.
                        return;
                    }

                    // Todo:: record all failed subscription payment
                }
            }

        } catch (\Exception $e) {
            print_r($e->getMessage());
            // Todo:: record the exception in subscription log.
        }
	}

    /**
     * Pay diver commissions.
     *
     * @param $subscription
     * @param $driver
     */
    private function payCommission($subscription, $driver) {
        $from = $subscription->current_period_start;
        $to   = $subscription->current_period_end;

        // Get driver total sales ($from -> $to).
        $sales = new SaleCollection($driver->sales($from, $to));

        if (!is_null($sales->toArray()['totals']['commission'])) {

            \DB::beginTransaction();
            if ($commissionPayment = $this->paypal->commissionPayment($sales->toArray()['totals']['commission'], $driver)) {

                // Todo:: record PayPal commission payment success.
                $commission_payment = [
                    'driver_id' => $driver->id,
                    'commissions' => $commissionPayment->amount->total,
                    'currency' => $commissionPayment->amount->currency,
                    'status' => 'completed',
                    'commission_ipn' => $commissionPayment,
                    'commission_payment_id' => $commissionPayment->id,
                    'commission_start' => $subscription->current_period_start,
                    'commission_end' => $subscription->current_period_end
                ];

                if ($commission = EloquentCommissionRepository::create($commission_payment)) {

                    \DB::commit();

                    // Todo:: fire notification event the commission paid.
                }

            } else {

                // Todo:: record commission PayPal payment failed.
                $commission_payment = [
                    'driver_id' => $driver->id,
                    'commissions' => $sales->toArray()['totals']['commission'],
                    'currency' => $driver->currency,
                    'status' => 'pending',
                    'commission_start' => $subscription->current_period_start,
                    'commission_end' => $subscription->current_period_end
                ];

                if ($commission = EloquentCommissionRepository::create($commission_payment)) {

                    \DB::commit();

                }
            }
        }
    }

}
