<?php namespace App\Http\Controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;
use DateTime;
use Carbon\Carbon;
use App\Models\Room;

class ExportController extends Controller {

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth');
	}


    public function index()
    {
        return view('export');
    }

    /**
     * Download the spreadsheet for any period.
     *
     * @return Response
     */
    public function download()
    {
        $room = Room::findOrFail(Input::get('room', 1));
        $humiditySensors = array_merge(explode(',', $room->humidity_sensor_names), explode(',', $room->external_humidity_sensor_names), ['humid15']);
        $temperatureSensors = array_merge(explode(',', $room->temperature_sensor_names), explode(',', $room->external_temperature_sensor_names), ['tem15']);
        $powerSensors = explode(',', $room->power_sensor_names);


        $startDate = Input::has('startDate') ? Carbon::createFromFormat('d/m/Y', Input::get('startDate')) : null;
        $endDate = Input::has('endDate') ? Carbon::createFromFormat('d/m/Y', Input::get('endDate')) : null;

        // Check we have 2 dates
        if ($startDate == null) {
            return back()->withInput()->withErrors(['startDate' => 'Start date is invalid']);
        }
        else if ($endDate == null) {
            return back()->withInput()->withErrors(['endDate' => 'End date is invalid']);
        }

        // Check no. of days is less than 30
        $noOfDays = $startDate->diffInDays($endDate);
        if ($noOfDays > 30) {
            return back()->withInput()->withErrors(['startDate' => 'Maximum period is 30 days']);
        }

        // Set time to make dates inclusive
        $startDate->hour = 0;
        $startDate->minute = 0;
        $startDate->second = 0;
        $endDate->hour = 23;
        $endDate->minute = 59;
        $endDate->second = 59;
        $dateRange = [$startDate, $endDate];

        $filename = 'ccm ';
        if ($noOfDays == 0) {
            $filename = $filename.$startDate->toDateString();
        }
        else {
            $filename = $filename.$startDate->toDateString().' to '.$filename.$endDate->toDateString();
        }

        //
        $groupBy = null;
        if ($noOfDays > 7) {
            $groupBy = 'ROUND((UNIX_TIMESTAMP(recorded_at)+900) / 1800)'; // 30 minute intervals
        }
        else if ($noOfDays > 0) {
            $groupBy = 'ROUND((UNIX_TIMESTAMP(recorded_at)+900) / 600)'; // 10 minute intervals
        }

        // Save as excel
        Excel::create($filename, function($excel) use ($dateRange, $groupBy, $humiditySensors, $temperatureSensors, $powerSensors) {

            // Set the title
            $excel->setTitle('Climate Comfort Monitoring');
            $excel->setCreator('Mobile Computing Lab');

            $excel->sheet('Humidity', function($sheet) use ($dateRange, $groupBy, $humiditySensors) {

                $query = DB::table('humidity_pivot')->whereBetween('recorded_at',$dateRange)->orderBy('recorded_at');
                if ($groupBy != null) {
                    $select = 'MAX(recorded_at) recorded_at';
                    foreach($humiditySensors as $sensor) {
                        $select = $select.', AVG('.$sensor.') '.$sensor;
                    }
                    $query->groupBy(DB::raw($groupBy))->select(DB::raw($select));
                }
                $results = $query->get();

                $sheet->setOrientation('landscape');

                $sheet->appendRow(array_merge(['date/time'],$humiditySensors));
                foreach ($results as $item) {
                    $date = new DateTime($item->recorded_at);
                    $date->modify('+7 hour');
                    $row = [$date->format('Y-m-d H:i:s')];
                    foreach ($humiditySensors as $sensor) {
                        $row[] = $item->{$sensor};
                    }
                    $sheet->appendRow($row);
                }
            });

            $excel->sheet('Temperature', function($sheet) use ($dateRange, $groupBy, $temperatureSensors) {

                $query = DB::table('temperature_pivot')->whereBetween('recorded_at',$dateRange)->orderBy('recorded_at');
                if ($groupBy != null) {
                    $select = 'MAX(recorded_at) recorded_at';
                    foreach($temperatureSensors as $sensor) {
                        $select = $select.', AVG('.$sensor.') '.$sensor;
                    }
                    $query->groupBy(DB::raw($groupBy))->select(DB::raw($select));
                }
                $results = $query->get();

                $sheet->setOrientation('landscape');

                $sheet->appendRow(array_merge(['date/time'],$temperatureSensors));
                foreach ($results as $item) {
                    $date = new DateTime($item->recorded_at);
                    $date->modify('+7 hour');
                    $row = [$date->format('Y-m-d H:i:s')];
                    foreach ($temperatureSensors as $sensor) {
                        $row[] = $item->{$sensor};
                    }
                    $sheet->appendRow($row);
                }

            });

            $excel->sheet('Power', function($sheet) use ($dateRange, $groupBy, $powerSensors) {

                $sheet->setOrientation('landscape');

                $query = DB::table('power_pivot')->whereBetween('recorded_at',$dateRange)->orderBy('recorded_at');
                if ($groupBy != null) {
                    $select = 'MAX(recorded_at) recorded_at';
                    foreach($powerSensors as $sensor) {
                        $select = $select.', SUM('.$sensor.') '.$sensor;
                    }
                    $query->groupBy(DB::raw($groupBy))->select(DB::raw($select));
                }
                $results = $query->get();

                $sheet->setOrientation('landscape');

                $sheet->appendRow(array_merge(['date/time'],$powerSensors));
                foreach ($results as $item) {
                    $date = new DateTime($item->recorded_at);
                    $date->modify('+7 hour');
                    $row = [$date->format('Y-m-d H:i:s')];
                    foreach ($powerSensors as $sensor) {
                        $row[] = $item->{$sensor};
                    }
                    $sheet->appendRow($row);
                }
            });

        })->download('xlsx');
    }

	/**
	 * Download the spreadsheet for fixed period.
	 *
	 * @return Response
	 */
	public function downloadFixed()
	{
		$room = Room::findOrFail(Input::get('room', 1));

        $filename = 'ccm24h';
		$sqlForPeriod = 'DATE_SUB(NOW(),INTERVAL 24 HOUR)';
		if (Input::get('period') == 'week') {
            $filename = 'ccm7days';
			$sqlForPeriod = 'DATE_SUB(NOW(),INTERVAL 7 DAY)';
		}
		else if (Input::get('period') == 'month') {
			$filename = 'ccm30days';
            $sqlForPeriod = 'DATE_SUB(NOW(),INTERVAL 30 DAY)';
		}

        // Humidity data
        $humiditySensors = array_merge(explode(',', $room->humidity_sensor_names), explode(',', $room->external_humidity_sensor_names), ['humid15']);
        $humidities = DB::table('humidity')->whereIn('sensor',$humiditySensors)
            ->where('recorded_at','>=',DB::raw($sqlForPeriod))->orderBy('recorded_at')->get();
        $humidityResults = [];
        foreach($humidities as $row) {
            if (!isset($humidityResults[$row->recorded_at])) {
                $humidityResults[$row->recorded_at] = [];
            }
            $humidityResults[$row->recorded_at][$row->sensor] = $row->value;
        }
        $humidities = null; // Free mem

        set_time_limit(30);

        // Temperature data
        $temperatureSensors = array_merge(explode(',', $room->temperature_sensor_names), explode(',', $room->external_temperature_sensor_names), ['tem15']);
        $temperatures = DB::table('temperature')->whereIn('sensor',$temperatureSensors)
            ->where('recorded_at','>=',DB::raw($sqlForPeriod))
            ->orderBy('recorded_at')->get();
        $temperatureResults = [];
        foreach($temperatures as $row) {
            if (!isset($temperatureResults[$row->recorded_at])) {
                $temperatureResults[$row->recorded_at] = [];
            }
            $temperatureResults[$row->recorded_at][$row->sensor] = $row->value;
        }
        $temperatures = null;

        set_time_limit(30);

        // Power data
        $powerSensors = explode(',', $room->power_sensor_names);
        $powers = DB::table('power')->whereIn('sensor',$powerSensors)
            ->where('recorded_at','>=',DB::raw($sqlForPeriod))
            ->orderBy('recorded_at')->get();
        $powerResults = [];
        foreach($powers as $row) {
            if (!isset($powerResults[$row->recorded_at])) {
                $powerResults[$row->recorded_at] = [];
            }
            $powerResults[$row->recorded_at][$row->sensor] = $row->value;
        }
        $powers = null;

        set_time_limit(30);

        // Save as excel
		Excel::create($filename, function($excel) use ($humiditySensors, $humidityResults,
            $temperatureSensors, $temperatureResults, $powerSensors, $powerResults) {

			// Set the title
			$excel->setTitle('Climate Comfort Monitoring');
			$excel->setCreator('Mobile Computing Lab');
			
		    $excel->sheet('Humidity', function($sheet) use ($humiditySensors, $humidityResults) {

		        $sheet->setOrientation('landscape');
				$sheet->appendRow(array_merge(['date/time'],$humiditySensors));
				foreach ($humidityResults as $recorded_at => $sensorValues) {
					$date = new DateTime($recorded_at);
					$date->modify('+7 hour');
					$row = [$date->format('Y-m-d H:i:s')];
					foreach ($humiditySensors as $sensor) {
						$row[] = $sensorValues[$sensor];
					}
					$sheet->appendRow($row);
				}
		    });
			
		    $excel->sheet('Temperature', function($sheet) use ($temperatureSensors, $temperatureResults) {

		        $sheet->setOrientation('landscape');
				$sheet->appendRow(array_merge(['date/time'],$temperatureSensors));
				foreach($temperatureResults as $recorded_at => $sensorValues) {
					$date = new DateTime($recorded_at);
					$date->modify('+7 hour');
					$row = [$date->format('Y-m-d H:i:s')];
					foreach ($temperatureSensors as $sensor) {
						$row[] = $sensorValues[$sensor];
					}
					$sheet->appendRow($row);
				}
		    });
			
		    $excel->sheet('Power', function($sheet) use ($powerSensors, $powerResults) {

		        $sheet->setOrientation('landscape');
				$sheet->appendRow(array_merge(['date/time'],$powerSensors));
				foreach($powerResults as $recorded_at => $sensorValues) {
					$date = new DateTime($recorded_at);
					$date->modify('+7 hour');
					$row = [$date->format('Y-m-d H:i:s')];
					foreach ($powerSensors as $sensor) {
						$row[] = $sensorValues[$sensor];
					}
					$sheet->appendRow($row);
				}
		    });

		})->download('xlsx');
	}

}
