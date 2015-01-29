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
        $columns = ['sales', 'ranking', 'stock_reminder', 'billing', 'emails'];

        array_map(function($column) {
            Schema::table('notifications', function($table) use ($column)
            {
                $table->dropColumn($column);
            });
        }, $columns);

        Schema::table('notifications', function($table)
        {
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
            $table->dropForeign('notifications_type_id_foreign');
            $table->dropColumn('value');
        });
	}

}
