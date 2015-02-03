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

        Schema::table('commissions', function($table)
        {
            $table->timestamp('commission_start')->nullable();
            $table->timestamp('commission_end')->nullable();
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
            $table->dropColumn(['commission_start', 'commission_end']);
        });
	}

}
