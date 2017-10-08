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

Route::get('/', function () {
    return view('welcome');
});

Route::post('/register', ['uses' => 'Account\RegisterController@register']);
Route::post('/login', ['uses' => 'Account\LoginController@login']);
Route::post('/getInfoCharacter', ['uses' => 'Character\CharacterController@getInfoCharacter']);
Route::get('/getSMSService', ['uses' => 'Account\AccountController@getSMSService']);

Route::group(['prefix' => 'account'], function () {
    Route::post('/changeAccountInfoUseSMS', ['uses' => 'Account\AccountController@changeAccountInfoUseSMS']);
});

Route::group(['prefix' => 'bank'], function () {
    Route::get('/getBankInfo', ['uses' => 'Bank\BankController@getBankInfo']);
    Route::post('/bankTransfer', ['uses' => 'Bank\BankController@bankTransfer']);
    Route::post('/changeMoney', ['uses' => 'Bank\BankController@changeMoney']);
    Route::post('/buyItemSliver', ['uses' => 'Bank\BankController@buyItemSliver']);
    Route::post('/sellItemSliver', ['uses' => 'Bank\BankController@sellItemSliver']);
    Route::post('/jewelAction', ['uses' => 'Bank\BankController@jewelAction']);
});

Route::group(['prefix' => 'event'], function () {
    Route::post('/checkInEventList', ['uses' => 'Event\EventController@getEventList']);
    Route::post('/addCheckIn', ['uses' => 'Event\EventController@addCheckIn']);
});