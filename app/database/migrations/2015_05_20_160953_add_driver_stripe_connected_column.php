<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDriverStripeConnectedColumn extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('drivers', function (Blueprint $table) {
            $table->boolean('stripe_enabled')->default(false);
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('drivers', function($table)
        {
            $table->dropColumn('stripe_enabled');
        });
	}

}
