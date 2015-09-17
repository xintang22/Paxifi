<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterIncreaseCountryIsoLength extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('countries', function($table)
        {
            DB::statement('ALTER table countries MODIFY COLUMN iso VARCHAR(5)');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('countries', function($table)
        {
            DB::statement('ALTER table countries MODIFY COLUMN iso VARCHAR(2)');
        });
	}

}
