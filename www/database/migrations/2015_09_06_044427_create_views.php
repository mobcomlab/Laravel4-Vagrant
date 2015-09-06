<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateViews extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::statement('create or replace view humidity_pivot as (
          select
            recorded_at,
            sum(case when sensor = "humid1" then value end) as humid1,
            sum(case when sensor = "humid2" then value end) as humid2,
            sum(case when sensor = "humid3" then value end) as humid3,
            sum(case when sensor = "humid4" then value end) as humid4,
            sum(case when sensor = "humid5" then value end) as humid5,
            sum(case when sensor = "humid6" then value end) as humid6,
            sum(case when sensor = "humid7" then value end) as humid7,
            sum(case when sensor = "humid8" then value end) as humid8,
            sum(case when sensor = "humid9" then value end) as humid9,
            sum(case when sensor = "humid10" then value end) as humid10,
            sum(case when sensor = "humid11" then value end) as humid11,
            sum(case when sensor = "humid12" then value end) as humid12,
            sum(case when sensor = "humid13" then value end) as humid13,
            sum(case when sensor = "humid14" then value end) as humid14,
            sum(case when sensor = "humid15" then value end) as humid15
          from humidity
          group by recorded_at
        )');

        DB::statement('create or replace view temperature_pivot as (
          select
            recorded_at,
            sum(case when sensor = "tem1" then value end) as tem1,
            sum(case when sensor = "tem2" then value end) as tem2,
            sum(case when sensor = "tem3" then value end) as tem3,
            sum(case when sensor = "tem4" then value end) as tem4,
            sum(case when sensor = "tem5" then value end) as tem5,
            sum(case when sensor = "tem6" then value end) as tem6,
            sum(case when sensor = "tem7" then value end) as tem7,
            sum(case when sensor = "tem8" then value end) as tem8,
            sum(case when sensor = "tem9" then value end) as tem9,
            sum(case when sensor = "tem10" then value end) as tem10,
            sum(case when sensor = "tem11" then value end) as tem11,
            sum(case when sensor = "tem12" then value end) as tem12,
            sum(case when sensor = "tem13" then value end) as tem13,
            sum(case when sensor = "tem14" then value end) as tem14,
            sum(case when sensor = "tem15" then value end) as tem15
          from temperature
          group by recorded_at
        )');

        DB::statement('create or replace view power_pivot as (
          select
            recorded_at,
            sum(case when sensor = "sc5_214_60a" then value end) as sc5_214_60a,
            sum(case when sensor = "sc5_214_25a" then value end) as sc5_214_25a
          from power
          group by recorded_at
        );');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        DB::statement('drop view humidity_pivot');
        DB::statement('drop view temperature_pivot');
        DB::statement('drop view power_pivot');
	}

}
