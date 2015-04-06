<?php namespace App\Http\Controllers;

use Illuminate\Support\Facades\Config;
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
	public function day()
	{
		Excel::create('ccm24h', function($excel) {

			// Set the title
			$excel->setTitle('Climate Comfort Monitoring (Last 24 hours)');
			$excel->setCreator('Mobile Computing Lab')->setCompany('Mobile Computing Lab');
			
		    $excel->sheet('Humidity', function($sheet) {

		        $sheet->setOrientation('landscape');
				
				$humiditySensors = ['room7','room10','room11','room12'];
				$humidities = DB::table('humidity')->whereIn('sensor',$humiditySensors)
									->where('recorded_at','>=',DB::raw('DATE_SUB(NOW(),INTERVAL 23 HOUR)'))
									->orderBy('recorded_at')->get();
				
				$results = [];
				foreach($humidities as $row) {
					if ($results[$row->recorded_at] == null) {
						$results[$row->recorded_at] = [];
					}
					$results[$row->recorded_at][$row->sensor] = $row->value;
				}
				
				dd($results);
				
				$sheet->appendRow(array_merge(['Recorded'],$humiditySensors));
				foreach($results as $recorded_at => $sensorValues) {
					$row = [$recorded_at];
					foreach ($humiditySensors as $sensor) {
						$row[] = $sensorValues[$sensor];
					}
					$sheet->appendRow($row);
				}

		    });

		})->download('xls');

	}

}
