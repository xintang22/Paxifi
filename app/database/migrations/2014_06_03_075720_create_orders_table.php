<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('orders', function(Blueprint $table)
		{
            $table->increments('id');

            $table->unsignedInteger('payment_id');

            $table->unsignedInteger('total_items');
            $table->decimal('total_costs');
            $table->decimal('total_sales');

            $table->string('buyer_email')->nullable();
            $table->tinyInteger('feedback')->default(0)->nullable();
            $table->text('comment')->nullable();

            $table->boolean('status')->default(0);

            $table->timestamps();

            $table->foreign('payment_id')->references('id')->on('payments');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('orders');
	}

}