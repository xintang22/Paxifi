<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeNotificationsTableStructure extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (Schema::hasTable('notifications'))
        {
            Schema::drop('notifications');
        }

        Schema::create('notifications', function($table)
        {
            $table->increments('id');
            $table->unsignedInteger('driver_id');
            $table->unsignedInteger('type_id');
            $table->string('value');

            $table->timestamps();
            $table->softDeletes();

            // Foreign key.
            $table->foreign('driver_id')->references('id')->on('drivers');
            $table->foreign('type_id')->references('id')->on('notification_types');
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
