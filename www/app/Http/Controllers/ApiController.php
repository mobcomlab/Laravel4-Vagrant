<?php namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use DateTime;
use App\Models\Room;

class ApiController extends Controller {

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{

	}

	/**
	 * Show the current stats.
	 *
	 * @return Response
	 */
	public function now()
	{
		if (Config::get('app.debug')) {
			DB::enableQueryLog();
		}
		
		$room = Room::find(Input::get('room', 1));
		if ($room == null) {
			return response()->json(['success' => false, 'message' => 'Room not found']);
		}
		
		// Humidity of sensor room1
		$humiditySensors = explode(',', $room->humidity_sensor_names);
		$humidityMaxDate = DB::table('humidity')->whereIn('sensor',$humiditySensors)->max('recorded_at');
		$currentHumidity = DB::table('humidity')->whereIn('sensor',$humiditySensors)
							->where('recorded_at',$humidityMaxDate)->avg('value');
		
		// Humidity of sensor external
		$humidityExternalSensorName = explode(',', $room->external_humidity_sensor_names);
		$humidityExternalMaxDate = DB::table('humidity')->where('sensor',$humidityExternalSensorName)
							->max('recorded_at');
		$currentHumidityExternal = DB::table('humidity')->where('sensor',$humidityExternalSensorName)
							->where('recorded_at',$humidityExternalMaxDate)->pluck('value');

		// Temp of sensor room1
		$temperatureSensors = explode(',', $room->temperature_sensor_names);
		$temperatureMaxDate = DB::table('temperature')->whereIn('sensor',$temperatureSensors)->max('recorded_at');
		$currentTemperature = DB::table('temperature')->whereIn('sensor',$temperatureSensors)
							->where('recorded_at',$temperatureMaxDate)->avg('value');
		
		// Temp of sensor external
		$temperatureExternalSensorName = explode(',', $room->external_temperature_sensor_names);
		$temperatureExternalMaxDate = DB::table('temperature')->where('sensor',$temperatureExternalSensorName)
							->max('recorded_at');
		$currentTemperatureExternal = DB::table('temperature')->where('sensor',$temperatureExternalSensorName)
							->where('recorded_at',$temperatureExternalMaxDate)->pluck('value');
	
		// Power
		$today = Carbon::today();
		$powerSensors = explode(',', $room->power_sensor_names);
		$powerMaxDate = DB::table('power')->whereIn('sensor',$powerSensors)->max('recorded_at');
		$powerNow = DB::table('power')->whereIn('sensor',$powerSensors)
							->where('recorded_at',$powerMaxDate)->sum('value');
		$powerDayReadingCount1 = DB::table('power')->where('sensor',$powerSensors[0])
						->whereBetween('recorded_at',[$today->copy()->subHours(7),$today->copy()->addHours(17)])->get();
		$powerDayReadingCount2 = DB::table('power')->where('sensor',$powerSensors[1])
						->whereBetween('recorded_at',[$today->copy()->subHours(7),$today->copy()->addHours(17)])->get();
		$powerDayReadingCount = 0;
		for ($i = 0; $i < count($powerDayReadingCount1); $i++) {
			$sum = $powerDayReadingCount1[$i]->value + $powerDayReadingCount2[$i]->value;
			if ($sum > 0) {
				$powerDayReadingCount++;
			}
		}
		if ($powerDayReadingCount > 0) {
			$powerDayAverage = DB::table('power')->whereIn('sensor',$powerSensors)
							->whereBetween('recorded_at',[$today->copy()->subHours(7),$today->copy()->addHours(17)])->sum('value') / $powerDayReadingCount;
			$powerDayHoursUsed = $powerDayReadingCount / 60 / 60;
			$powerDayEnergyKWH = $powerDayAverage * $powerDayHoursUsed;
		}
		else {
			$powerDayAverage = 0;
			$powerDayHoursUsed = 0;
			$powerDayEnergyKWH = 0;
		}
	
		// Occupancy
		$occupancy = DB::table('occupancy')->where('start','<=',new DateTime)->where('end','>=', new DateTime)->first();
	
		$result = [
			'success' => true,
			'humidity' => ['value' => $currentHumidity, 'recorded_at' => $humidityMaxDate, 'sensors' => $humiditySensors],
			'external_humidity' => ['value' => $currentHumidityExternal, 'recorded_at' => $humidityExternalMaxDate, 'sensor' => $humidityExternalSensorName],
			'temperature' => ['value' => $currentTemperature, 'recorded_at' => $temperatureMaxDate, 'sensors' => $temperatureSensors],
			'external_temperature' => ['value' => $currentTemperatureExternal, 'recorded_at' => $temperatureExternalMaxDate, 'sensor' => $temperatureExternalSensorName],
			'power' => ['value' => $powerNow, 'recorded_at' => $powerMaxDate, 'sensors' => $powerSensors, 
				'day' => ['average_kw' => $powerDayAverage, 'hours_used' => $powerDayHoursUsed, 'energy_kwh' => $powerDayEnergyKWH]],
			'occupancy' => $occupancy];
			
		if (Config::get('app.debug')) {
			$result['db_queries'] = DB::getQueryLog();
		}
		return response()->json($result);
	}


	public function dayHumidTemp()
	{
		$room = Room::find(Input::get('room', 1));
		if ($room == null) {
			return response()->json(['success' => false, 'message' => 'Room not found']);
		}

		$temperatureSensors = explode(',', $room->temperature_sensor_names);
		$temperatures = DB::table('temperature')->whereIn('sensor',$temperatureSensors)
			->select(DB::raw('CONCAT(DATE(recorded_at),\' \',MAKETIME(HOUR(recorded_at),0,0)) recorded_at_hour, AVG(value) value'))
			->where('recorded_at','>=',DB::raw('DATE_SUB(NOW(),INTERVAL 23 HOUR)'))
			->groupBy('recorded_at_hour')->orderBy('recorded_at_hour')->get();

		$humiditySensors = explode(',', $room->humidity_sensor_names);
		$humidities = DB::table('humidity')->whereIn('sensor',$humiditySensors)
				->select(DB::raw('CONCAT(DATE(recorded_at),\' \',MAKETIME(HOUR(recorded_at),0,0)) recorded_at_hour, AVG(value) value'))
				->where('recorded_at','>=',DB::raw('DATE_SUB(NOW(),INTERVAL 23 HOUR)'))
				->groupBy('recorded_at_hour')->orderBy('recorded_at_hour')->get();

		$occupancies = DB::select('SELECT CONCAT(DATE(a.recorded_at),\' \',MAKETIME(HOUR(a.recorded_at),0,0)) recorded_at,
			IFNULL(o.people,0) people FROM (
   			 	SELECT date_sub(now(),interval 23 hour) recorded_at UNION
   			 	SELECT date_sub(now(),interval 22 hour) UNION
   			 	SELECT date_sub(now(),interval 21 hour) UNION
   			 	SELECT date_sub(now(),interval 20 hour) UNION
   			 	SELECT date_sub(now(),interval 19 hour) UNION
   			 	SELECT date_sub(now(),interval 18 hour) UNION
   			 	SELECT date_sub(now(),interval 17 hour) UNION
   			 	SELECT date_sub(now(),interval 16 hour) UNION
   			 	SELECT date_sub(now(),interval 15 hour) UNION
   			 	SELECT date_sub(now(),interval 14 hour) UNION
   			 	SELECT date_sub(now(),interval 13 hour) UNION
   			 	SELECT date_sub(now(),interval 12 hour) UNION
   			 	SELECT date_sub(now(),interval 11 hour) UNION
   			 	SELECT date_sub(now(),interval 10 hour) UNION
   			 	SELECT date_sub(now(),interval 9 hour) UNION
   			 	SELECT date_sub(now(),interval 8 hour) UNION
   			 	SELECT date_sub(now(),interval 7 hour) UNION
   			 	SELECT date_sub(now(),interval 6 hour) UNION
   			 	SELECT date_sub(now(),interval 5 hour) UNION
   			 	SELECT date_sub(now(),interval 4 hour) UNION
   			 	SELECT date_sub(now(),interval 3 hour) UNION
   			 	SELECT date_sub(now(),interval 2 hour) UNION
   			 	SELECT date_sub(now(),interval 1 hour) UNION
   			 	SELECT now()
			) a
			LEFT JOIN occupancy o ON a.recorded_at between o.start and o.end ORDER BY recorded_at');

		// Results as 2D array
		$results = [['Hour','Temperature','Humidity']];

		$tempIndex = 0;
		$humidIndex = 0;

		for ($i = 0; $i < count($occupancies); $i++) {
			$recorded_at = $occupancies[$i]->recorded_at;
			$temperature = null;
			$humidity = null;

			if ($tempIndex < count($temperatures) && $temperatures[$tempIndex]->recorded_at_hour == $recorded_at) {
				$temperature = round($temperatures[$tempIndex]->value,1);
				$tempIndex++;
			}

			if ($humidIndex < count($humidities) && $humidities[$humidIndex]->recorded_at_hour == $recorded_at) {
				$humidity = round($humidities[$humidIndex]->value,1);
				$humidIndex++;
			}

			$results[] = [$recorded_at,$temperature,$humidity];
		}

		return response()->json(['results' => $results]);

	}

	public function dayPower()
	{
		$room = Room::find(Input::get('room', 1));
		if ($room == null) {
			return response()->json(['success' => false, 'message' => 'Room not found']);
		}

		$powerSensors = explode(',', $room->power_sensor_names);
		$powers = DB::select('SELECT concat(date(recorded_at),\' \',maketime(hour(recorded_at),0,0)) recorded_at_hour,
			sum(value) value FROM (
   	 			SELECT recorded_at, sum(value) value FROM power
				WHERE sensor in (?,?) AND recorded_at >= date_sub(now(),interval 23 hour)
				GROUP BY recorded_at) a
			GROUP BY recorded_at_hour ORDER BY recorded_at_hour', $powerSensors);

		$occupancies = DB::select('SELECT CONCAT(DATE(a.recorded_at),\' \',MAKETIME(HOUR(a.recorded_at),0,0)) recorded_at,
			IFNULL(o.people,0) people FROM (
   			 	SELECT date_sub(now(),interval 23 hour) recorded_at UNION
   			 	SELECT date_sub(now(),interval 22 hour) UNION
   			 	SELECT date_sub(now(),interval 21 hour) UNION
   			 	SELECT date_sub(now(),interval 20 hour) UNION
   			 	SELECT date_sub(now(),interval 19 hour) UNION
   			 	SELECT date_sub(now(),interval 18 hour) UNION
   			 	SELECT date_sub(now(),interval 17 hour) UNION
   			 	SELECT date_sub(now(),interval 16 hour) UNION
   			 	SELECT date_sub(now(),interval 15 hour) UNION
   			 	SELECT date_sub(now(),interval 14 hour) UNION
   			 	SELECT date_sub(now(),interval 13 hour) UNION
   			 	SELECT date_sub(now(),interval 12 hour) UNION
   			 	SELECT date_sub(now(),interval 11 hour) UNION
   			 	SELECT date_sub(now(),interval 10 hour) UNION
   			 	SELECT date_sub(now(),interval 9 hour) UNION
   			 	SELECT date_sub(now(),interval 8 hour) UNION
   			 	SELECT date_sub(now(),interval 7 hour) UNION
   			 	SELECT date_sub(now(),interval 6 hour) UNION
   			 	SELECT date_sub(now(),interval 5 hour) UNION
   			 	SELECT date_sub(now(),interval 4 hour) UNION
   			 	SELECT date_sub(now(),interval 3 hour) UNION
   			 	SELECT date_sub(now(),interval 2 hour) UNION
   			 	SELECT date_sub(now(),interval 1 hour) UNION
   			 	SELECT now()
			) a
			LEFT JOIN occupancy o ON a.recorded_at between o.start and o.end ORDER BY recorded_at');

		// Results as 2D array
		$results = [['Hour','Energy']];

		$powerIndex = 0;
		for ($i = 0; $i < count($occupancies); $i++) {
			$recorded_at = $occupancies[$i]->recorded_at;

			$powerHourEnergyKWH = null;

			if ($powerIndex < count($powers) && $powers[$powerIndex]->recorded_at_hour == $recorded_at) {
				$powerHourEnergyKWH = round($powers[$powerIndex]->value/60,1);
				$powerIndex++;
			}

			$results[] = [$recorded_at,$powerHourEnergyKWH];
		}

		return response()->json(['results' => $results]);

	}

	public function weekHumidTemp()
	{
		$room = Room::find(Input::get('room', 1));
		if ($room == null) {
			return response()->json(['success' => false, 'message' => 'Room not found']);
		}
		$temperatureSensors = explode(',', $room->temperature_sensor_names);
		$temperatures = DB::table('temperature')->whereIn('sensor',$temperatureSensors)
				->select(DB::raw('CONCAT(DATE(recorded_at),\' \',MAKETIME(HOUR(recorded_at),0,0)) recorded_at_hour, AVG(value) value'))
				->where('recorded_at','>=',DB::raw('DATE_SUB(NOW(),INTERVAL 7 DAY)'))
				->groupBy('recorded_at_hour')->orderBy('recorded_at_hour')->get();

		$humiditySensors = explode(',', $room->humidity_sensor_names);
		$humidities = DB::table('humidity')->whereIn('sensor',$humiditySensors)
				->select(DB::raw('CONCAT(DATE(recorded_at),\' \',MAKETIME(HOUR(recorded_at),0,0)) recorded_at_hour, AVG(value) value'))
				->where('recorded_at','>=',DB::raw('DATE_SUB(NOW(),INTERVAL 7 DAY)'))
				->groupBy('recorded_at_hour')->orderBy('recorded_at_hour')->get();


		$occupancies = DB::select('SELECT CONCAT(DATE(a.recorded_at),\' \',MAKETIME(HOUR(a.recorded_at),0,0)) recorded_at,
			IFNULL(o.people,0) people FROM (
   			 	SELECT date_sub(now(),interval 23 hour) recorded_at UNION
   			 	SELECT date_sub(now(),interval 22 hour) UNION
   			 	SELECT date_sub(now(),interval 21 hour) UNION
   			 	SELECT date_sub(now(),interval 20 hour) UNION
   			 	SELECT date_sub(now(),interval 19 hour) UNION
   			 	SELECT date_sub(now(),interval 18 hour) UNION
   			 	SELECT date_sub(now(),interval 17 hour) UNION
   			 	SELECT date_sub(now(),interval 16 hour) UNION
   			 	SELECT date_sub(now(),interval 15 hour) UNION
   			 	SELECT date_sub(now(),interval 14 hour) UNION
   			 	SELECT date_sub(now(),interval 13 hour) UNION
   			 	SELECT date_sub(now(),interval 12 hour) UNION
   			 	SELECT date_sub(now(),interval 11 hour) UNION
   			 	SELECT date_sub(now(),interval 10 hour) UNION
   			 	SELECT date_sub(now(),interval 9 hour) UNION
   			 	SELECT date_sub(now(),interval 8 hour) UNION
   			 	SELECT date_sub(now(),interval 7 hour) UNION
   			 	SELECT date_sub(now(),interval 6 hour) UNION
   			 	SELECT date_sub(now(),interval 5 hour) UNION
   			 	SELECT date_sub(now(),interval 4 hour) UNION
   			 	SELECT date_sub(now(),interval 3 hour) UNION
   			 	SELECT date_sub(now(),interval 2 hour) UNION
   			 	SELECT date_sub(now(),interval 1 hour) UNION
   			 	SELECT now()
			) a
			LEFT JOIN occupancy o ON a.recorded_at between o.start and o.end ORDER BY recorded_at');

		// Results as 2D array
		$results = [['Day','Temperature','Humidity']];

		$tempIndex = 0;
		$humidIndex = 0;
		for ($i = 6; $i >= 0; $i--) {
			$recorded_at = null;
			$temperature = null;
			$humidity = null;
			$temp_count = 0;
			$humid_count = 0;
			for ($j = 0; $j < count($occupancies); $j++) {
				$recorded_at = Carbon::parse($occupancies[$j]->recorded_at)->subDays($i);

				if ($tempIndex < count($temperatures) && (Carbon::parse($temperatures[$tempIndex]->recorded_at_hour)->addHour() == $recorded_at)) {
					$temperature = $temperature + $temperatures[$tempIndex]->value;
					$tempIndex++;
					$temp_count++;
				}

				if ($humidIndex < count($humidities) && (Carbon::parse($humidities[$humidIndex]->recorded_at_hour)->addHour() == $recorded_at)) {
					$humidity = $humidity + $humidities[$humidIndex]->value;
					$humidIndex++;
					$humid_count++;
				}
			}
			if ($temp_count != 0) {
				$temperature = $temperature / $temp_count;
			}
			if ($temperature != null) {
				$temperature = round($temperature,1);
			}
			if ($humid_count != 0) {
				$humidity = $humidity / $humid_count;
			}
			if ($humidity != null) {
				$humidity = round($humidity,1);
			}

			$results[] = [$i,$temperature,$humidity];
		}

		return response()->json(['results' => $results]);

	}

	public function weekPower()
	{
		$room = Room::find(Input::get('room', 1));
		if ($room == null) {
			return response()->json(['success' => false, 'message' => 'Room not found']);
		}
		$powerSensors = explode(',', $room->power_sensor_names);

		// Results as 2D array
		$results = [['Day','Energy']];

		for ($i = 6; $i >= 0; $i--) {
			$date = Carbon::today()->copy()->subDays($i);
			$powerDayAverage = null;
			$powerDayReadingCount1 = DB::table('power')->where('sensor',$powerSensors[0])
				->whereBetween('recorded_at',[$date->copy()->subHours(7),$date->copy()->addHours(17)])->get();
			$powerDayReadingCount2 = DB::table('power')->where('sensor',$powerSensors[1])
				->whereBetween('recorded_at',[$date->copy()->subHours(7),$date->copy()->addHours(17)])->get();
			$powerDayReadingCount = 0;
			for ($j = 0; $j < count($powerDayReadingCount1); $j++) {
				$sum = $powerDayReadingCount1[$j]->value + $powerDayReadingCount2[$j]->value;
				if ($sum > 0) {
					$powerDayReadingCount++;
				}
			}
			if ($powerDayReadingCount > 0) {
				$powerDayAverage = round(DB::table('power')->whereIn('sensor',$powerSensors)
						->whereBetween('recorded_at',[$date->copy()->subHours(7),$date->copy()->addHours(17)])->sum('value') / $powerDayReadingCount,1);
				$powerDayHoursUsed = $powerDayReadingCount / 60 / 60;
				$powerDayEnergyKWH = $powerDayAverage * $powerDayHoursUsed;
			} else {
				$powerDayEnergyKWH = 0;
			}
			$results[] = [$i,$powerDayEnergyKWH];
		}

		return response()->json(['results' => $results]);

	}
}
