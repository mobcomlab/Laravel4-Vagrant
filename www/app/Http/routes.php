<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', 'WelcomeController@index');

Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
]);
//day
Route::get('api/day/content', 'ApiController@dayContent');
Route::get('api/day/temperature', 'ApiController@dayTemperature');
Route::get('api/day/humidity', 'ApiController@dayHumidity');
Route::get('api/day/power', 'ApiController@dayPower');
//week
Route::get('api/week/content', 'ApiController@weekContent');
Route::get('api/week/temperature', 'ApiController@weekTemperature');
Route::get('api/week/humidity', 'ApiController@weekHumidity');
Route::get('api/week/power', 'ApiController@weekPower');
//month
Route::get('api/month', 'ApiController@month');

Route::get('export', ['as' => 'export', 'uses' => 'ExportController@index']);
Route::post('export/download', ['as' => 'download', 'uses' => 'ExportController@download']);

Route::get('/{date}', ['as' => 'date', 'uses' => 'WelcomeController@date']);
