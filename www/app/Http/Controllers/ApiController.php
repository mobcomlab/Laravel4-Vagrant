<?php namespace App\Http\Controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use DateTime;

class ApiController extends Controller {

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
	 * Show the current stats.
	 *
	 * @return Response
	 */
	public function now()
	{
		if (Config::get('app.debug')) {
			DB::enableQueryLog();
		}
		
		// Humidity of sensor room1
		$humiditySensors = ['room7','room10','room11','room12'];
		$humidityMaxDate = DB::table('humidity')->whereIn('sensor',$humiditySensors)->max('recorded_at');
		$currentHumidity = DB::table('humidity')->whereIn('sensor',$humiditySensors)
							->where('recorded_at',$humidityMaxDate)->avg('value');
		
		// Humidity of sensor external
		$humidityExternalSensorName = 'external';
		$humidityExternalMaxDate = DB::table('humidity')->where('sensor',$humidityExternalSensorName)
							->max('recorded_at');
		$currentHumidityExternal = DB::table('humidity')->where('sensor',$humidityExternalSensorName)
							->where('recorded_at',$humidityExternalMaxDate)->pluck('value');

		// Temp of sensor room1
		$temperatureSensors = ['room7','room10','room11','room12'];
		$temperatureMaxDate = DB::table('temperature')->whereIn('sensor',$temperatureSensors)->max('recorded_at');
		$currentTemperature = DB::table('temperature')->whereIn('sensor',$temperatureSensors)
							->where('recorded_at',$temperatureMaxDate)->avg('value');
		
		// Temp of sensor external
		$temperatureExternalSensorName = 'external';
		$temperatureExternalMaxDate = DB::table('temperature')->where('sensor',$temperatureExternalSensorName)
							->max('recorded_at');
		$currentTemperatureExternal = DB::table('temperature')->where('sensor',$temperatureExternalSensorName)
							->where('recorded_at',$temperatureExternalMaxDate)->pluck('value');
	
		// Power
		$powerSensors = ['sc5_213_60a','sc5_213_25a'];
		$powerMaxDate = DB::table('power')->whereIn('sensor',$powerSensors)->max('recorded_at');
		$powerNow = DB::table('power')->whereIn('sensor',$powerSensors)
							->where('recorded_at',$powerMaxDate)->sum('value');
		$powerDayReadingCount = DB::table('power')->whereIn('sensor',$powerSensors)->where('value', '>', 0)
							->where('recorded_at','>',new DateTime('today'))->count(DB::raw('DISTINCT recorded_at'));
		if ($powerDayReadingCount > 0) {
			$powerDayAverage = DB::table('power')->whereIn('sensor',$powerSensors)->where('value', '>', 0)
							->where('recorded_at','>',new DateTime('today'))->sum('value') / $powerDayReadingCount;
			$powerDayHoursUsed = $powerDayReadingCount / 60;
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
	
	public function day()
	{
		
		/* SELECT CONCAT( DATE( recorded_at ) ,  ' ', MAKETIME( HOUR( recorded_at ) , 0, 0 ) ) recorded_at_hour, AVG( value ) value_avg
FROM  `temperature` 
WHERE sensor =  'room1'
AND recorded_at >= DATE_SUB( NOW( ) , INTERVAL 23 HOUR ) 
GROUP BY recorded_at_hour
ORDER BY recorded_at_hour
		*/
		
		$humiditySensors = ['room7','room10','room11','room12'];
		$humidities = DB::table('humidity')->whereIn('sensor',$humiditySensors)
							->select(DB::raw('CONCAT(DATE(recorded_at),\' \',MAKETIME(HOUR(recorded_at),0,0)) recorded_at_hour, AVG(value) value'))
							->where('recorded_at','>=',DB::raw('DATE_SUB(NOW(),INTERVAL 23 HOUR)'))
							->groupBy('recorded_at_hour')->orderBy('recorded_at_hour')->get();

		$temperatureSensors = ['room7','room10','room11','room12'];
		$temperatures = DB::table('temperature')->whereIn('sensor',$temperatureSensors)
							->select(DB::raw('CONCAT(DATE(recorded_at),\' \',MAKETIME(HOUR(recorded_at),0,0)) recorded_at_hour, AVG(value) value'))
							->where('recorded_at','>=',DB::raw('DATE_SUB(NOW(),INTERVAL 23 HOUR)'))
							->groupBy('recorded_at_hour')->orderBy('recorded_at_hour')->get();
		
		$powerSensors = ['sc5_213_60a','sc5_213_25a'];
		$powers = DB::select('SELECT concat(date(recorded_at),\' \',maketime(hour(recorded_at),0,0)) recorded_at_hour, 
			avg(value) value FROM (
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
		
		/* // As objects
		$results = $occupancies;
		
		$humidIndex = 0;
		$tempIndex = 0;
		$powerIndex = 0;
		for ($i = 0; $i < count($results); $i++) {
			$recorded_at = $results[$i]->recorded_at;
			
			if ($humidIndex < count($humidities) && $humidities[$humidIndex]->recorded_at_hour == $recorded_at) {
				$results[$i]->humidity = $humidities[$humidIndex]->value;
				$humidIndex++;
			}
			
			if ($tempIndex < count($temperatures) && $temperatures[$tempIndex]->recorded_at_hour == $recorded_at) {
				$results[$i]->temperature = $temperatures[$tempIndex]->value;
				$tempIndex++;
			}
			
			if ($powerIndex < count($powers) && $powers[$powerIndex]->recorded_at_hour == $recorded_at) {
				$results[$i]->power = $powers[$powerIndex]->value;
				$powerIndex++;
			}
		}
		*/
		
		// Results as 2D array
		$results = [['Hour','People','Humidity','Temperature','Power']];
		
		$humidIndex = 0;
		$tempIndex = 0;
		$powerIndex = 0;
		for ($i = 0; $i < count($occupancies); $i++) {
			$recorded_at = $occupancies[$i]->recorded_at;
			
			$people = $occupancies[$i]->people;
			$temperature = null;
			$humidity = null;
			$power = 0;
			
			if ($humidIndex < count($humidities) && $humidities[$humidIndex]->recorded_at_hour == $recorded_at) {
				$humidity = $humidities[$humidIndex]->value;
				$humidIndex++;
			}
			if ($tempIndex < count($temperatures) && $temperatures[$tempIndex]->recorded_at_hour == $recorded_at) {
				$temperature = $temperatures[$tempIndex]->value;
				$tempIndex++;
			}
			if ($powerIndex < count($powers) && $powers[$powerIndex]->recorded_at_hour == $recorded_at) {
				$power = $powers[$powerIndex]->value;
				$powerIndex++;
			}
			
			$results[] = [$recorded_at,$people,$humidity,$temperature,$power];
		}
		
		return response()->json(['results' => $results]);
		
	}

}
