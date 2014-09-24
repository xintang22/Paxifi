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

            $table->tinyInteger('status')->default(0);
            $table->longText('details');
            $table->string('paypal_transaction_id')->nullable()->default("");
            $table->boolean('paypal_transaction_status')->nullable()->default(0);
            $table->text('ipn')->nullable()->default("");

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
