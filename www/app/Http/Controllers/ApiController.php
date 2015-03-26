<?php namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

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
		// Humidity of sensor room1
		$humiditySensorName = 'room1';
		$humidityMaxDate = DB::table('humidity')->where('sensor',$humiditySensorName)->max('recorded_at');
		$currentHumidity = DB::table('humidity')->where('sensor',$humiditySensorName)
							->where('recorded_at',$humidityMaxDate)->pluck('value');
		
		// Temp of sensor room1
		$temperatureSensorName = 'room1';
		$temperatureMaxDate = DB::table('temperature')->where('sensor',$temperatureSensorName)->max('recorded_at');
		$currentTemperature = DB::table('temperature')->where('sensor',$temperatureSensorName)
							->where('recorded_at',$temperatureMaxDate)->pluck('value');
		
		// Temp of sensor external
		$temperatureExternalSensorName = 'external';
		$temperatureExternalMaxDate = DB::table('temperature')->where('sensor',$temperatureExternalSensorName)
							->max('recorded_at');
		$currentTemperatureExternal = DB::table('temperature')->where('sensor',$temperatureExternalSensorName)
							->where('recorded_at',$temperatureExternalMaxDate)->pluck('value');
	
		// Power
		$powerSensorName = 'sc5_213_60a';
		$powerMaxDate = DB::table('power')->where('sensor',$powerSensorName)->max('recorded_at');
		$currentPower = DB::table('power')->where('sensor',$powerSensorName)
							->where('recorded_at',$powerMaxDate)->pluck('value');
	
		$result = [
			'humidity' => ['value' => $currentHumidity, 'recorded_at' => $humidityMaxDate, 'sensor' => $humiditySensorName],
			'temperature' => ['value' => $currentTemperature, 'recorded_at' => $temperatureMaxDate, 'sensor' => $temperatureSensorName],
			'external_temperature' => ['value' => $currentTemperatureExternal, 'recorded_at' => $temperatureExternalMaxDate, 'sensor' => $temperatureExternalSensorName],
			'power' => ['value' => $currentPower, 'recorded_at' => $powerMaxDate, 'sensor' => $powerSensorName],
			'occupancy' => ['value' => null]];
		return response()->json($result);
	}

}
