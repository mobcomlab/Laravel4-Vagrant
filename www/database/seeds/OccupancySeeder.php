<?php

use Illuminate\Database\Seeder;

class OccupancySeeder extends Seeder {

    public function run()
    {
        DB::table('occupancy')->delete();

		DB::table('occupancy')->insert([
		    ['room_id' => 1, 'description' => '261108 Principles of Physics II', 'start' => '2015-04-06 10:00:00', 'end' => '2015-04-06 11:59:59', 'people' => 98],
		    ['room_id' => 1, 'description' => '252112 Calculus', 'start' => '2015-04-06 13:00:00', 'end' => '2015-04-06 14:59:59', 'people' => 80],
		    ['room_id' => 1, 'description' => '258212 Cell Biology', 'start' => '2015-04-06 15:00:00', 'end' => '2015-04-06 16:59:59', 'people' => 102],
		    ['room_id' => 1, 'description' => '261352 Modern Physics', 'start' => '2015-04-07 08:00:00', 'end' => '2015-04-07 09:59:59', 'people' => 63],
		    ['room_id' => 1, 'description' => '354426 Education for Social Development', 'start' => '2015-04-08 13:00:00', 'end' => '2015-04-08 16:00:00', 'people' => 95],
		    ['room_id' => 1, 'description' => '261352 Modern Physics', 'start' => '2015-04-09 11:00:00', 'end' => '2015-04-09 11:59:59', 'people' => 63],
		    ['room_id' => 1, 'description' => '261108 Principles of Physics II', 'start' => '2015-04-10 08:00:00', 'end' => '2015-04-10 08:59:59', 'people' => 98],
		    ['room_id' => 1, 'description' => '252112 Calculus', 'start' => '2015-04-10 13:00:00', 'end' => '2015-04-10 14:59:59', 'people' => 80]
		]);
    }

}