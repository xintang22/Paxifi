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

            $table->string('currency', 3)->default('USD');
            $table->decimal('amount');

            $table->boolean('status');
            $table->longText('details');

            $table->timestamps();

            $table->foreign('payment_method_id')->references('id')->on('payment_methods');
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
