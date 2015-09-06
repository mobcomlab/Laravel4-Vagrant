<?php

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder {

    public function run()
    {
        DB::table('users')->delete();

		DB::table('users')->insert([
		    ['id' => 1, 'email' => 'admin@ccm.mobcomlab.com', 'password' => bcrypt('admin'), 'name' => 'Administrator']
		]);
    }

}