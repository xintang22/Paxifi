<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProblemsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('problems', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('problem_type_id');
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('payment_id');
            $table->string('reporter_email');

            $table->timestamps();

            // Foreign key.
            $table->foreign('problem_type_id')->references('id')->on('problem_types');
            $table->foreign('product_id')->references('id')->on('products');
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
	    Schema::drop('problems');
	}

}
