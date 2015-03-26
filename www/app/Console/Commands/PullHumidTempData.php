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
		$url = 'http://www.etbuilding.sci.nu.ac.th/dir.html?id=2&file=database_2015_3_7.db&action=download';
		$client = new HttpClient();
		$response = $client->get($url, ['save_to' => storage_path().'/humidtemp.sqlite']);
		$this->info('Downloaded latest database');
		
		// HUMIDITY
		$humiditySources = [
			'Humid1_5' => [ 'humid1' => 'room1',
							'humid2' => 'room2',
							'humid3' => 'room3',
							'humid4' => 'room4',
							'humid5' => 'room5'],
			'Humid6_10' => ['humid6' => 'room6',
							'humid7' => 'room7',
							'humid8' => 'room8',
							'humid9' => 'room9',
							'humid10' => 'room10'],
			'Humid11_15' => ['humid11' => 'room11',
							'humid12' => 'room12',
							'humid13' => 'room13',
							'humid14' => 'external',
							'humid15' => 'corridor']];
		
		// Last updated date
		$humidityMinDate = strtotime(DB::table('humidity')->max('recorded_at'));
		
		foreach (array_keys($humiditySources) as $tableName) {
		
			// Query only new records
			$humidityQuery = DB::connection('humidtemp')->table($tableName)->select(DB::raw('*, (substr(date,7,4) || \'-\' || substr(date,4,2) || \'-\' || substr(date,1,2) || \' \' || time) date_time'));
	
			if ($humidityMinDate) {
				// time_since_min_date is time passed since $humidityMinDate
				$humidityQuery->addSelect(DB::raw('julianday(substr(date,7,4) || \'-\' || substr(date,4,2) || \'-\' || substr(date,1,2) || \' \' || time) - julianday(\''.date('Y-m-d H:i:s', $humidityMinDate).'\') time_since_min_date'));
				$humidityQuery->whereRaw('time_since_min_date > 0');
			}
			$humidityData = $humidityQuery->get();
		
			// Insert each new row into target db
			$records = [];
			foreach ($humidityData as $row) {
				// Create the records based on the source map
				foreach (array_keys($humiditySources[$tableName]) as $columnName) {
					$sensorName = $humiditySources[$tableName][$columnName];
					$record = ['recorded_at' => $row->date_time, 'sensor' => $sensorName, 'value' => $row->{$columnName}];
					$records[] = $record;
				}
			}
			DB::table('humidity')->insert($records);
			$this->info('Inserted '.count($records).' new humidity records from '.$tableName);
		}

		// TEMPERATURE
		$temperatureSources = [
			'Tem1_5' => [ 'tem1' => 'room1',
							'tem2' => 'room2',
							'tem3' => 'room3',
							'tem4' => 'room4',
							'tem5' => 'room5'],
			'Tem6_10' => ['tem6' => 'room6',
							'tem7' => 'room7',
							'tem8' => 'room8',
							'tem9' => 'room9',
							'tem10' => 'room10'],
			'Tem11_15' => ['tem11' => 'room11',
							'tem12' => 'room12',
							'tem13' => 'room13',
							'tem14' => 'external',
							'tem15' => 'corridor']];
		// Last updated date
		$temperatureMinDate = strtotime(DB::table('temperature')->max('recorded_at'));
		
		foreach (array_keys($temperatureSources) as $tableName) {
			// Query only new records
			$temperatureQuery = DB::connection('humidtemp')->table($tableName)->select(DB::raw('*, (substr(date,7,4) || \'-\' || substr(date,4,2) || \'-\' || substr(date,1,2) || \' \' || time) date_time'));
			if ($temperatureMinDate) {
				// time_since_min_date is time passed since $temperatureMinDate
				$temperatureQuery->addSelect(DB::raw('julianday(substr(date,7,4) || \'-\' || substr(date,4,2) || \'-\' || substr(date,1,2) || \' \' || time) - julianday(\''.date('Y-m-d H:i:s', $temperatureMinDate).'\') time_since_min_date'));
				$temperatureQuery->whereRaw('time_since_min_date > 0');
			}
			$temperatureData = $temperatureQuery->get();
		
			// Insert each new row into target db
			$records = [];
			foreach($temperatureData as $row) {
				foreach (array_keys($temperatureSources[$tableName]) as $columnName) {
					$sensorName = $temperatureSources[$tableName][$columnName];
					$record = ['recorded_at' => $row->date_time, 'sensor' => $sensorName, 'value' => $row->{$columnName}];
					$records[] = $record;
				}
			}
			DB::table('temperature')->insert($records);
			$this->info('Inserted '.count($records).' new temperature records from '.$tableName);
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
