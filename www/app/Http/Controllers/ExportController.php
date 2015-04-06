<?php namespace App\Http\Controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use DateTime;

class ExportController extends Controller {

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('guest');
	}

	/**
	 * Download the spreadsheet for the last 24 hours.
	 *
	 * @return Response
	 */
	public function download()
	{
		
		$sqlForPeriod = 'DATE_SUB(NOW(),INTERVAL 24 HOUR)';
		if (Input::get('period') == 'week') {
			$sqlForPeriod = 'DATE_SUB(NOW(),INTERVAL 7 DAY)';
		}
		else if (Input::get('period') == 'month') {
			$sqlForPeriod = 'DATE_SUB(NOW(),INTERVAL 30 DAY)';
		}
		
		Excel::create('ccm24h', function($excel) use ($sqlForPeriod) {

			// Set the title
			$excel->setTitle('Climate Comfort Monitoring (Last 24 hours)');
			$excel->setCreator('Mobile Computing Lab')->setCompany('Mobile Computing Lab');
			
		    $excel->sheet('Humidity', function($sheet) use ($sqlForPeriod) {

		        $sheet->setOrientation('landscape');
				
				$humiditySensors = ['room7','room10','room11','room12','corridor','external'];
				$humidities = DB::table('humidity')->whereIn('sensor',$humiditySensors)
									->where('recorded_at','>=',DB::raw($sqlForPeriod))
									->orderBy('recorded_at')->get();
				
				$results = [];
				foreach($humidities as $row) {
					if (!isset($results[$row->recorded_at])) {
						$results[$row->recorded_at] = [];
					}
					$results[$row->recorded_at][$row->sensor] = $row->value;
				}
				
				set_time_limit(60);
				$sheet->appendRow(array_merge(['date/time'],$humiditySensors));
				foreach($results as $recorded_at => $sensorValues) {
					$row = [$recorded_at];
					foreach ($humiditySensors as $sensor) {
						$row[] = $sensorValues[$sensor];
					}
					$sheet->appendRow($row);
				}
				set_time_limit(60);

		    });
			
		    $excel->sheet('Temperature', function($sheet) use ($sqlForPeriod) {

		        $sheet->setOrientation('landscape');
				
				$temperatureSensors = ['room7','room10','room11','room12','corridor','external'];
				$temperatures = DB::table('temperature')->whereIn('sensor',$temperatureSensors)
									->where('recorded_at','>=',DB::raw($sqlForPeriod))
									->orderBy('recorded_at')->get();
				
				$results = [];
				foreach($temperatures as $row) {
					if (!isset($results[$row->recorded_at])) {
						$results[$row->recorded_at] = [];
					}
					$results[$row->recorded_at][$row->sensor] = $row->value;
				}
				
				set_time_limit(60);
				$sheet->appendRow(array_merge(['date/time'],$temperatureSensors));
				foreach($results as $recorded_at => $sensorValues) {
					$row = [$recorded_at];
					foreach ($temperatureSensors as $sensor) {
						$row[] = $sensorValues[$sensor];
					}
					$sheet->appendRow($row);
				}
				set_time_limit(60);

		    });
			
		    $excel->sheet('Power', function($sheet) use ($sqlForPeriod) {

		        $sheet->setOrientation('landscape');
				
				$powerSensors = ['sc5_213_60a','sc5_213_25a'];
				$powers = DB::table('power')->whereIn('sensor',$powerSensors)
									->where('recorded_at','>=',DB::raw($sqlForPeriod))
									->orderBy('recorded_at')->get();
				
				$results = [];
				foreach($powers as $row) {
					if (!isset($results[$row->recorded_at])) {
						$results[$row->recorded_at] = [];
					}
					$results[$row->recorded_at][$row->sensor] = $row->value;
				}
				
				set_time_limit(60);
				$sheet->appendRow(array_merge(['date/time'],$powerSensors));
				foreach($results as $recorded_at => $sensorValues) {
					$row = [$recorded_at];
					foreach ($powerSensors as $sensor) {
						$row[] = $sensorValues[$sensor];
					}
					$sheet->appendRow($row);
				}
				set_time_limit(60);

		    });

		})->download('xls');

	}

}
