<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommissionsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('commissions', function(Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('driver_id');
            $table->float('commissions');
            $table->string('currency');
            $table->enum('status', ['completed', 'pending'])->default('pending');
            $table->string('commission_payment_id')-nullable();
            $table->text('commission_ipn')->nullable();

            $table->timestamps();

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
        Schema::drop('commissions');
    }

}
