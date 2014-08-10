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

            $table->unsignedInteger('total_items')->nullable()->default(0);
            $table->decimal('total_costs')->nullable()->default(0);
            $table->decimal('total_sales')->nullable()->default(0);
            $table->decimal('total_tax')->nullable()->default(0);
            $table->decimal('profit')->nullable()->default(0);
            $table->decimal('commission')->default(0);

            $table->string('buyer_email')->nullable()->default(NULL);

            $table->boolean('status')->nullable()->default(0);

            $table->timestamps();
            $table->softDeletes();
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
