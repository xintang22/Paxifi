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
            $table->enum('status', ['Shipped', 'Waiting' , 'Printed'])->default('Waiting');
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
