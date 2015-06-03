<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDriverPaymentMethodsPivotTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('driver_payment_methods', function(Blueprint $table)
        {
            $table->unsignedInteger('driver_id');
            $table->unsignedInteger('payment_method_id');
            $table->timestamps();

            $table->primary(array('driver_id', 'payment_method_id'));

            // Foreign keys
            $table->foreign('driver_id')->references('id')->on('drivers');
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('driver_payment_methods');
    }

}
