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
Route::post('/login', ['uses' => 'Account\LoginController@login'])->middleware('checkAction');;
Route::post('/getInfoCharacter', ['uses' => 'Character\CharacterController@getInfoCharacter']);
Route::get('/getSMSService', ['uses' => 'Account\AccountController@getSMSService']);
Route::get('/getResetInfo', ['uses' => 'Character\ResetController@getResetInfo']);

Route::group(['prefix' => 'account'], function () {
    Route::post('/changeAccountInfoUseSMS', ['uses' => 'Account\AccountController@changeAccountInfoUseSMS']);
});

Route::group(['prefix' => 'character', 'middleware' => ['checkAction']], function () {
    Route::post('/deleteInventory', ['uses' => 'Character\CharacterController@deleteInventory']);
    Route::post('/addPoint', ['uses' => 'Character\CharacterController@addPoint']);
    Route::post('/resetPoint', ['uses' => 'Character\CharacterController@resetPoint']);
    Route::post('/resetSkillMaster', ['uses' => 'Character\CharacterController@resetSkillMaster']);
    Route::post('/lockItem', ['uses' => 'Character\CharacterController@lockItem']);
    Route::post('/clearPK', ['uses' => 'Character\CharacterController@clearPK']);
    Route::post('/moveLorencia', ['uses' => 'Character\CharacterController@moveLorencia']);
//    Route::post('/resetCharacter', ['uses' => 'Character\ResetController@resetCharacter']);
    Route::post('/reset', ['uses' => 'Character\ResetController@resetCharacter']);
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

Route::group(['prefix' => 'webshop'], function () {
    Route::post('/getItemWareHouseList', ['uses' => 'Account\WebShopController@getItemWareHouseList']);
    Route::post('/addItemToSuperMarket', ['uses' => 'Account\WebShopController@addItemToSuperMarket']);
    Route::post('/getItemInSuperMarketList', ['uses' => 'Account\WebShopController@getItemInSuperMarketList']);
    Route::post('/buyItemSuperMarket', ['uses' => 'Account\WebShopController@buyItemInSuperMarket']);
    Route::post('/itemWebShopList', ['uses' => 'Account\WebShopController@getItemWebShopList']);
    Route::post('/buyItemWebShop', ['uses' => 'Account\WebShopController@buyItemWebShop']);
});

Route::group(['prefix' => 'ranking'], function () {
    Route::get('/getRankAll', ['uses' => 'Rank\RankController@getRankAll']);
    Route::get('/getRankGuild', ['uses' => 'Rank\RankController@getRankGuild']);
    Route::get('/getCharInGuild', ['uses' => 'Rank\RankController@getCharInGuild']);
    Route::get('/getRankDay', ['uses' => 'Rank\RankController@getRankDay']);
    Route::get('/getRankTop', ['uses' => 'Rank\RankController@getRankTop']);
});