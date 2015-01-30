<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SetDriverActiveDefault extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        DB::statement("AlTER table drivers ALTER COLUMN status SET DEFAULT TRUE");
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        DB::statement("AlTER table drivers ALTER COLUMN status SET DEFAULT FALSE");
	}

}
