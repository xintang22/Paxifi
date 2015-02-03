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
        Schema::table('drivers', function($table)
        {
            DB::statement('ALTER table drivers MODIFY COLUMN address TEXT NULL');
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
            DB::statement('ALTER table drivers MODIFY COLUMN address TEXT NOT NULL');
        });
	}

}
