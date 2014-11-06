<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatedStatusFieldInCommissionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        $columns = ['capture_created_at', 'capture_updated_at', 'capture_id', 'capture_ipn', 'capture_status', 'status'];

        foreach($columns as $index => $column) {
            if (Schema::hasColumn('commissions', $column))
            {
                Schema::table('commissions', function($table) use ($column)
                {
                    $table->dropColumn($column);
                });
            }
        }

        Schema::table('commissions', function($table) {
            $table->enum('status', ['completed', 'pending'])->default('pending');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('commissions', function($table)
        {
            $table->dropColumn('status');
        });
	}

}
