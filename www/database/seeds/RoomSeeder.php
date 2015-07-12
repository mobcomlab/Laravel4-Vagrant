<?php

use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder {

    public function run()
    {
        DB::table('room')->delete();

		DB::table('room')->insert([
		    ['id' => 1, 'building' => 'SC5', 'number' => '214', 'temperature_sensor_names' => 'tem1,tem2,tem3,tem4,tem5,tem6,tem7,tem8,tem9,tem10,tem11,tem12,tem13', 'humidity_sensor_names' => 'humid1,humid2,humid3,humid4,humid5,humid6,humid7,humid8,humid9,humid10,humid11,humid12,humid13', 'external_temperature_sensor_names' => 'tem14', 'external_humidity_sensor_names' => 'humid14', 'power_sensor_names' => 'sc5_214_60a,sc5_214_25a']
		]);
    }

}