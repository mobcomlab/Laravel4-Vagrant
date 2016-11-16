<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client as HttpClient;
use DateTime;

class PullHumidTempData extends Command {

	const URL = 'http://www.etbuilding.sci.nu.ac.th/tem1,tem2,tem3,tem4,tem5,tem6,tem7,tem8,tem9,tem10,tem11,tem12,tem13,tem14,tem15,humid1,humid2,humid3,humid4,humid5,humid6,humid7,humid8,humid9,humid10,humid11,humid12,humid13,humid14,humid15.vars';

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
	
	public function fire()
	{
		$recordedAt = new DateTime;
		
		// Grab the data
		$client = new HttpClient();
		$response = $client->get(self::URL.'?cache='.str_random(40));
		$data = explode("\n", $response->getBody());
		
		// Process lines
		$humidityRecords = [];
		$temperatureRecords = [];
		foreach ($data as $line) {
			if (preg_match('/^(\w+)=(\d+\.\d+)$/', $line, $matches)) {
				$sensorName = $matches[1];
				$sensorValue = $matches[2];
				$record = ['recorded_at' => $recordedAt, 'sensor' => $sensorName, 'value' => $sensorValue];
				if (substr($sensorName, 0, 3) == 'hum') {
					$humidityRecords[] = $record;
				}
				else {
					$temperatureRecords[] = $record;
				}
			}
		}
		
		// Save to db
		DB::table('humidity')->insert($humidityRecords);
		$this->info('Inserted '.count($humidityRecords).' new humidity records.');
		DB::table('temperature')->insert($temperatureRecords);
		$this->info('Inserted '.count($temperatureRecords).' new temperature records.');
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
