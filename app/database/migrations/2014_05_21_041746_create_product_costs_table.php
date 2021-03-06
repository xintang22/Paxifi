<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductCostsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('product_costs', function(Blueprint $table)
		{
			$table->increments('id');
            $table->unsignedInteger('product_id');

            $table->decimal('unit_cost');
            $table->unsignedInteger('inventory');

            $table->timestamps();

            $table->unique(array('product_id', 'unit_cost'));

            // Foreign keys
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('product_costs');
	}

}
