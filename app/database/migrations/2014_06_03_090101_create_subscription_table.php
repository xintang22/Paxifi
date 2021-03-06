<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubscriptionTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('plan_id');
            $table->unsignedInteger('driver_id');

            $table->timestamp('trial_start')->nullable();
            $table->timestamp('trial_end')->nullable();

            $table->timestamp('start')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('ended_at')->nullable();

            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();

            $table->boolean('cancel_at_period_end')->default(0);

            // tracking subscription txn
            $table->text('ipn')->nullable()->default(null);
            $table->string('subscr_id')->nullable()->default(null);

            $table->enum('status', array('trialing', 'active', 'past_due', 'canceled'))->default('trialing');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('driver_id')->references('id')->on('drivers');
            $table->foreign('plan_id')->references('id')->on('subscription_plans');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('subscriptions');
    }

}
