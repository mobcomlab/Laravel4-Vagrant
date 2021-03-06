<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOccupancy extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('occupancy', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('room_id');
			$table->string('description');
			$table->timestamp('start');
			$table->timestamp('end');
			$table->integer('people');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('occupancy');
	}

}
