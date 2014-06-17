<?php

use Paxifi\Subscription\Repository\EloquentPlanRepository as Plan;

class SubscriptionPlansTableSeeder extends Seeder
{
    public function run()
    {
        /**
         * Truncate Product Tables before seed faker data
         */
        DB::table('subscription_plans')->truncate();

        Plan::create(
            array(
                'name' => 'Monthly subscription',
                'interval' => 'month',
                'amount' => 5,
                'currency' => 'USD',
                'interval_count' => 1,
                'trial_period_days' => 60,
            )
        );
    }
} 