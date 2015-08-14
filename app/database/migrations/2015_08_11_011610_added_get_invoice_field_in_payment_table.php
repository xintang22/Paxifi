<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddedGetInvoiceFieldInPaymentTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('payments', function (Blueprint $table) {
            $table->boolean('invoice')->default(false)->after('status');
            $table->string('invoice_email')->nullable()->after('invoice');
            $table->boolean('item_received')->default(false)->after('invoice_email');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['invoice', 'invoice_email', 'item_received']);
        });
	}

}
