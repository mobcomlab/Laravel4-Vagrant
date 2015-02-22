<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client as HttpClient;

class PullHumidTempData extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'pull:humidtemp';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Download humidity/temperature data from the sensor server and populate database with new data.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		// Disconnect the db (not sure if this is neccessary)
		DB::disconnect('humidtemp');

		// Download the latest sqlite datafile (save to disk)
		$url = 'http://www.etbuilding.sci.nu.ac.th/dir.html?id=2&file=database_2015_2_18.db&action=download';
		$client = new HttpClient();
		$response = $client->get($url, ['save_to' => storage_path().'/humidtemp.sqlite']);
		
		// HUMIDITY
		
		// Last updated date
		$humidityMinDate = strtotime(DB::table('humidity')->max('recorded_at'));
		
		// Query only new records
		$humidityQuery = DB::connection('humidtemp')->table('Humid_logging14')->select(DB::raw('*, (substr(date,7,4) || \'-\' || substr(date,4,2) || \'-\' || substr(date,1,2) || \' \' || time) date_time'));
	
		if ($humidityMinDate) {
			// time_since_min_date is time passed since $humidityMinDate
			$humidityQuery->addSelect(DB::raw('julianday(substr(date,7,4) || \'-\' || substr(date,4,2) || \'-\' || substr(date,1,2) || \' \' || time) - julianday(\''.date('Y-m-d H:i:s', $humidityMinDate).'\') time_since_min_date'));
			$humidityQuery->whereRaw('time_since_min_date > 0');
		}
		$humidityData = $humidityQuery->get();
		
		// Insert each new row into target db
		foreach($humidityData as $row) {
			DB::table('humidity')->insert(
			    ['recorded_at' => $row->date_time, 'sensor1' => $row->humid1, 'sensor2' => $row->humid2, 'sensor3' => $row->humid3, 'sensor4' => $row->humid4, 'sensor5' => $row->humid5, 'sensor6' => $row->humid6, 'sensor7' => $row->humid7, 'sensor8' => $row->humid8, 'sensor9' => $row->humid9, 'sensor10' => $row->humid10, 'sensor11' => $row->humid11, 'sensor12' => $row->humid12, 'sensor13' => $row->humid13, 'sensor14' => $row->humid14, 'sensor15' => $row->humid15]);
		}
		
		// TEMPERATURE
		
		// Last updated date
		$temperatureMinDate = strtotime(DB::table('temperature')->max('recorded_at'));
		
		// Query only new records
		$temperatureQuery = DB::connection('humidtemp')->table('Tem_logging14')->select(DB::raw('*, (substr(date,7,4) || \'-\' || substr(date,4,2) || \'-\' || substr(date,1,2) || \' \' || time) date_time'));
		if ($temperatureMinDate) {
			// time_since_min_date is time passed since $temperatureMinDate
			$temperatureQuery->addSelect(DB::raw('julianday(substr(date,7,4) || \'-\' || substr(date,4,2) || \'-\' || substr(date,1,2) || \' \' || time) - julianday(\''.date('Y-m-d H:i:s', $temperatureMinDate).'\') time_since_min_date'));
			$temperatureQuery->whereRaw('time_since_min_date > 0');
		}
		$temperatureData = $temperatureQuery->get();
		
		// Insert each new row into target db
		foreach($temperatureData as $row) {
			DB::table('temperature')->insert(
			    ['recorded_at' => $row->date_time, 'sensor1' => $row->tem1, 'sensor2' => $row->tem2, 'sensor3' => $row->tem3, 'sensor4' => $row->tem4, 'sensor5' => $row->tem5, 'sensor6' => $row->tem6, 'sensor7' => $row->tem7, 'sensor8' => $row->tem8, 'sensor9' => $row->tem9, 'sensor10' => $row->tem10, 'sensor11' => $row->tem11, 'sensor12' => $row->tem12, 'sensor13' => $row->tem13, 'sensor14' => $row->tem14, 'sensor15' => $row->tem15]);
		}
		
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [];
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [];
	}

}
