<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('driver_id');
            $table->unsignedInteger('category_id');

            $table->string('name');
            $table->mediumText('photos');
            $table->mediumText('description');

            $table->decimal('unit_price');
            $table->decimal('average_cost')->nullable();
            $table->unsignedInteger('inventory')->nullable();
            $table->decimal('tax_amount', 8, 5);

            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('products');
    }

}
