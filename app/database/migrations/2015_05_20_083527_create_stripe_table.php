<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStripeTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('stripe', function(Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('driver_id');
            $table->string('refresh_token');
            $table->string('token_type');
            $table->string('stripe_publishable_key');
            $table->string('stripe_user_id');
            $table->string('scope');
            $table->timestamps();
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('stripe');
	}

}
