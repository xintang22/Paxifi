<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('payments', function(Blueprint $table)
		{
			$table->increments('id');
            $table->unsignedInteger('payment_method_id');
            $table->unsignedInteger('order_id');

            $table->enum('status', ['Waiting', 'Received', 'Canceled', 'Completed']);
            $table->longText('details');

            $table->timestamps();
            $table->softDeletes();

            // Relationships.
            $table->foreign('payment_method_id')->references('id')->on('payment_methods');
            $table->foreign('order_id')->references('id')->on('orders');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('payments');
	}

}
