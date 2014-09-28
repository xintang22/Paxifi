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
            $table->enum('status', ['created', 'approved', 'failed', 'canceled', 'expired']);
            $table->string('commission_payment_id');
            $table->text('commission_ipn');

            // authorized commission capture
            $table->timestamp('capture_created_at')->nullable();
            $table->timestamp('capture_updated_at')->nullable();
            $table->string('capture_id')->nullable()->default("");
            $table->text('capture_ipn')->nullable()->default("");
            $table->string('capture_status')->nullable()->default("");

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
