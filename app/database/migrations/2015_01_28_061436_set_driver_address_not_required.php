<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SetDriverAddressNotRequired extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (Schema::hasTable('drivers'))
        {
            if (Schema::hasColumn('drivers', 'address'))
            {
                Schema::table('drivers', function($table)
                {
                    $table->dropColumn('address');
                });
            }
        }

        Schema::table('drivers', function($table)
        {
            $table->text('address')->nullable();
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('drivers', function($table)
        {
            $table->dropColumn('address');
        });
	}

}
