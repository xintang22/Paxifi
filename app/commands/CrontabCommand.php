<?php

use Illuminate\Console\Command;
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
	protected $name = 'paxifi:subscribe';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description.';

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

}
