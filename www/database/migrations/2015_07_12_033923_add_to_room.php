<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddToRoom extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('room', function(Blueprint $table)
		{
			$table->string('temperature_sensor_names');
			$table->string('humidity_sensor_names');
			$table->string('external_temperature_sensor_names');
			$table->string('external_humidity_sensor_names');
			$table->string('power_sensor_names');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('room', function(Blueprint $table)
		{
			$table->dropColumn('temperature_sensor_names');
			$table->dropColumn('humidity_sensor_names');
			$table->dropColumn('external_temperature_sensor_names');
			$table->dropColumn('external_humidity_sensor_names');
			$table->dropColumn('power_sensor_names');
		});
	}

}
