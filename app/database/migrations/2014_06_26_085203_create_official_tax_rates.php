<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfficialTaxRates extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('official_tax_rates', function(Blueprint $table)
		{
            $table->increments('id');
            $table->enum('category', array('standard', 'reduced'));
            $table->decimal('amount', 8, 5);
            $table->string('country');
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('postcode')->nullable();
            $table->boolean('included_in_price')->default(0);

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
		Schema::drop('official_tax_rates');
	}

}
