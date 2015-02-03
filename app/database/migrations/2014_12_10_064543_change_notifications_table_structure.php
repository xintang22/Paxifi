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
        Schema::table('notifications', function($table)
        {
            $table->dropColumn(['sales', 'ranking', 'stock_reminder', 'billing', 'emails']);

            $table->unsignedInteger('type_id');
            $table->string('value');

            // Foreign key.
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
        Schema::table('notifications', function($table) {
            $table->integer('sales')->default(0);
            $table->string('ranking')->nullable();
            $table->integer('stock_reminder')->default(0);
            $table->string('billing')->nullable();
            $table->string('emails')->nullable();

            $table->dropForeign('notifications_type_id_foreign');
            $table->dropColumn('value');
        });
	}

}
