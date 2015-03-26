<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHumidity extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('humidity', function(Blueprint $table)
		{
			$table->increments('id');
			$table->timestamp('recorded_at');
			$table->string('sensor',32);
			$table->float('value');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('humidity');
	}

}
