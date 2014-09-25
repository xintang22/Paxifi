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
            $table->float('total_commission');
            $table->enum('status', ['created', 'approved', 'failed', 'canceled', 'expired']);
            $table->string('commission_payment_id');
            $table->text('commission_ipn');
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
