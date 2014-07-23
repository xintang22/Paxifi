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

            $table->unsignedInteger('total_items')->default(0);
            $table->decimal('total_costs')->default(0);
            $table->decimal('total_sales')->default(0);
            $table->decimal('total_tax')->default(0);
            $table->decimal('profit')->default(0);
            $table->decimal('commission')->default(0);

            $table->string('buyer_email')->nullable();
            $table->tinyInteger('feedback')->default(0);
            $table->text('comment')->nullable();

            $table->boolean('status')->default(0);

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
