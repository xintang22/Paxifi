<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeedbackTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('driver_id');
            $table->unsignedInteger('payment_id')->unique();

            $table->tinyInteger('feedback')->nullable()->default(0);
            $table->text('comment')->nullable()->default(NULL);

            $table->timestamps();

            // Foreign key.
            $table->foreign('driver_id')->references('id')->on('drivers');
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
        Schema::drop('feedbacks');
	}

}
