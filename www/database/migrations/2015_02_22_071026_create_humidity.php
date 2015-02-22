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
			$table->float('sensor1');
			$table->float('sensor2');
			$table->float('sensor3');
			$table->float('sensor4');
			$table->float('sensor5');
			$table->float('sensor6');
			$table->float('sensor7');
			$table->float('sensor8');
			$table->float('sensor9');
			$table->float('sensor10');
			$table->float('sensor11');
			$table->float('sensor12');
			$table->float('sensor13');
			$table->float('sensor14');
			$table->float('sensor15');
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
