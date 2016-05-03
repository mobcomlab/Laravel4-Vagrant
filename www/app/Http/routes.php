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
Route::get('api/now', 'ApiController@now');
Route::get('api/day/humidtemp', 'ApiController@dayHumidTemp');
Route::get('api/day/power', 'ApiController@dayPower');
//week
Route::get('api/week/humidtemp', 'ApiController@weekHumidTemp');
Route::get('api/week/power', 'ApiController@weekPower');

Route::get('export', ['as' => 'export', 'uses' => 'ExportController@index']);
Route::post('export/download', ['as' => 'download', 'uses' => 'ExportController@download']);

Route::get('cast', ['as' => 'cast', 'uses' => 'CastController@index']);
Route::get('tv', ['as' => 'tv', 'uses' => 'WelcomeController@index']);
