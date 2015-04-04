<?php

use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder {

    public function run()
    {
        DB::table('room')->delete();

		DB::table('room')->insert([
		    ['id' => 1, 'building' => 'SC5', 'number' => '214']
		]);
    }

}