<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePushDevicesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('push_devices', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('driver_id');
            $table->string('token');
            $table->enum('type', ['ios', 'android']);
            $table->timestamps();

            $table->engine = 'InnoDB';
            $table->index('driver_id');
            $table->index('token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('push_devices');
    }

}
