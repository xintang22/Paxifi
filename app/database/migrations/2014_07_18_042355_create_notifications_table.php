<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('driver_id');
            $table->integer('sales')->nullable()->default(0);
            $table->string('ranking')->nullable()->default("");
            $table->integer('stock_reminder')->nullable()->default(0);
            $table->string('billing')->nullable()->default("");
            $table->string('emails')->nullable()->default("");
            $table->timestamps();
            $table->softDeletes();

            // Foreign key.
            $table->foreign('driver_id')->references('id')->on('drivers');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('notifications');
	}

}
