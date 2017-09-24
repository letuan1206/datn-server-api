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
Route::post('/getInfoCharacter', ['uses' => 'CharacterController@getInfoCharacter']);

Route::group(['prefix' => 'profile', 'middleware' => ['checkLogin']], function () {

    Route::post('/getProfile', ['uses' => 'ProfileController@getProfile']);
    Route::post('/changeNameMember', ['uses' => 'ProfileController@getChangeNameMember']);
    Route::post('/changePassword', ['uses' => 'ProfileController@getChangePassword']);
    Route::post('/changePassword2', ['uses' => 'ProfileController@getChangePassword2']);
    Route::post('/changeEmail', ['uses' => 'ProfileController@getChangeEmail']);
    Route::post('/changeQuestAnswer', ['uses' => 'ProfileController@getChangeQuestAnswer']);
    Route::post('/changeSnoNumber', ['uses' => 'ProfileController@getChangeSnoNumber']);
    Route::post('/changePhoneNumber', ['uses' => 'ProfileController@getChangePhoneNumber']);
    Route::post('/getSendInfoToEmail', ['uses' => 'ProfileController@getSendInfoToEmail']);
});