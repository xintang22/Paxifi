<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShipmentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('shipments', function(Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('sticker_id');
            $table->text('address');
            $table->enum('status', ['shipped', 'waiting' , 'printed'])->default('waiting');
            $table->string('paypal_payment_id')->nullable();
            $table->enum('paypal_payment_status', ['pending', 'completed'])->default('pending');
            $table->text('paypal_payment_details')->nullable();
            $table->timestamps();

            // Relationship.
            $table->foreign('sticker_id')->references('id')->on('stickers');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('shipments');
	}

}
