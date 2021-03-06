<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'HomeController@index')->name('home');
Route::get('/api/usage', 'HomeController@api_usage');
Route::get('/usage', 'HomeController@usage');
Route::get('/room/{room_id}', 'HomeController@room')->name('room');
Route::get('/room/{room_id}/pick', 'HomeController@room_pick')->name('room_pick');
Route::post('/room/{room_id}/pick', 'HomeController@room_picked')->name('room_picked');
