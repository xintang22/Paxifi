<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddedSalesAndCostFieldInOrderItemsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('order_items', function(Blueprint $table) {
            $table->decimal('unit_price')->default(0)->after('order_id');
            $table->decimal('average_cost')->default(0)->after('unit_price');
            $table->decimal('tax_amount', 8, 5)->default(0)->after('average_cost');
            $table->boolean('tax_enabled')->default(0)->after('tax_amount');
            $table->boolean('tax_included_in_price')->default(0)->after('tax_enabled');
            $table->string('currency')->default('USD')->after('tax_included_in_price');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn([
                'unit_price',
                'average_cost',
                'tax_amount',
                'tax_enabled',
                'tax_included_in_price',
                'currency'
            ]);
        });
	}

}
