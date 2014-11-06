<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDriversTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('seller_id')->unique()->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('photo')->nullable();
            $table->text('address');
            $table->string('currency', 3)->default('USD');
            $table->string('remember_token')->nullable();
            $table->integer('thumbs_up')->default(0);
            $table->integer('thumbs_down')->default(0);
            $table->string('paypal_account')->nullable();
            $table->string('paypal_refresh_token')->nullable();

            $table->boolean('tax_enabled')->default(false);
            $table->boolean('tax_included_in_price')->default(false);
            $table->decimal('tax_global_amount', 8, 5)->nullable();

            $table->boolean('notify_sale')->default(true);
            $table->boolean('notify_inventory')->default(true);
            $table->boolean('notify_feedback')->default(true);
            $table->boolean('notify_billing')->default(true);
            $table->boolean('notify_others')->default(true);
            $table->boolean('suspended')->default(false);
            $table->tinyInteger('status', false, true)->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('drivers');
    }

}
