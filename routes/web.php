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
Route::post('/login', ['uses' => 'Account\LoginController@login'])->middleware('checkAction');
Route::post('/getInfoCharacter', ['uses' => 'Character\CharacterController@getInfoCharacter'])->middleware('checkLoginToken');
Route::get('/getSMSService', ['uses' => 'Account\AccountController@getSMSService']);
Route::get('/getResetInfo', ['uses' => 'Character\ResetController@getResetInfo']);

Route::group(['prefix' => 'account'], function () {
    Route::post('/changeAccountInfoUseSMS', ['uses' => 'Account\AccountController@changeAccountInfoUseSMS']);
});

Route::group(['prefix' => 'character', 'middleware' => ['checkAction', 'checkLoginToken', 'checkOnline', 'checkSelectChar']], function () {
    Route::post('/deleteInventory', ['uses' => 'Character\CharacterController@deleteInventory']);
    Route::post('/addPoint', ['uses' => 'Character\CharacterController@addPoint']);
    Route::post('/resetPoint', ['uses' => 'Character\CharacterController@resetPoint']);
    Route::post('/resetSkillMaster', ['uses' => 'Character\CharacterController@resetSkillMaster']);
    Route::post('/lockItem', ['uses' => 'Character\CharacterController@lockItem']);
    Route::post('/clearPK', ['uses' => 'Character\CharacterController@clearPK']);
    Route::post('/moveLorencia', ['uses' => 'Character\CharacterController@moveLorencia']);
    Route::post('/changeClass', ['uses' => 'Character\CharacterController@changeClass']);
    Route::post('/reset', ['uses' => 'Character\ResetController@resetCharacter']);
    Route::post('/resetVIP', ['uses' => 'Character\ResetController@resetVipCharacter']);
    Route::post('/resetPO', ['uses' => 'Character\ResetController@resetVipPOCharacter']);
});

Route::group(['prefix' => 'bank', 'middleware' => ['checkLoginToken']], function () {
    Route::get('/getBankInfo', ['uses' => 'Bank\BankController@getBankInfo']);
    Route::post('/bankTransfer', ['uses' => 'Bank\BankController@bankTransfer']);
    Route::post('/changeMoney', ['uses' => 'Bank\BankController@changeMoney'])->middleware('checkOnline');
    Route::post('/buyItemSliver', ['uses' => 'Bank\BankController@buyItemSliver'])->middleware('checkOnline');
    Route::post('/sellItemSliver', ['uses' => 'Bank\BankController@sellItemSliver'])->middleware('checkOnline');
    Route::post('/jewelAction', ['uses' => 'Bank\BankController@jewelAction'])->middleware(['checkOnline', 'checkSelectChar']);
});

Route::group(['prefix' => 'event', 'middleware' => ['checkLoginToken']], function () {
    Route::post('/checkInEventList', ['uses' => 'Event\EventController@getEventList']);
    Route::post('/addCheckIn', ['uses' => 'Event\EventController@addCheckIn']);
});

Route::group(['prefix' => 'webshop', 'middleware' => ['checkLoginToken']], function () {
    Route::post('/getItemWareHouseList', ['uses' => 'Account\WebShopController@getItemWareHouseList']);
    Route::post('/addItemToSuperMarket', ['uses' => 'Account\WebShopController@addItemToSuperMarket'])->middleware('checkOnline');
    Route::post('/getItemInSuperMarketList', ['uses' => 'Account\WebShopController@getItemInSuperMarketList']);
    Route::post('/buyItemSuperMarket', ['uses' => 'Account\WebShopController@buyItemInSuperMarket'])->middleware('checkOnline');
    Route::post('/itemWebShopList', ['uses' => 'Account\WebShopController@getItemWebShopList']);
    Route::post('/buyItemWebShop', ['uses' => 'Account\WebShopController@buyItemWebShop'])->middleware('checkOnline');
});

Route::group(['prefix' => 'ranking', 'middleware' => []], function () {
    Route::get('/getRankAll', ['uses' => 'Rank\RankController@getRankAll']);
    Route::get('/getRankGuild', ['uses' => 'Rank\RankController@getRankGuild']);
    Route::get('/getCharInGuild', ['uses' => 'Rank\RankController@getCharInGuild']);
    Route::get('/getRankDay', ['uses' => 'Rank\RankController@getRankDay']);
    Route::get('/getRankTop', ['uses' => 'Rank\RankController@getRankTop'])->middleware('checkAction');
});

Route::group(['prefix' => 'card', 'middleware' => ['checkLoginToken']], function () {
    Route::post('/chargeCard', ['uses' => 'CardPhone\CardPhoneController@chargeCard']);
    Route::get('/getCardHistory', ['uses' => 'CardPhone\CardPhoneController@getCardHistory']);
});

Route::group(['prefix' => 'admin'], function () {
    Route::post('/login', ['uses' => 'Admin\AdminController@login']);

    Route::get('/dashBoard', ['uses' => 'Admin\AdminController@dashBoard']);
    Route::get('/getAccountCharacterOnline', ['uses' => 'Admin\AdminController@getAccountCharacterOnline']);

    Route::get('/accountList', ['uses' => 'Admin\AdminController@getAccountList']);
    Route::get('/accountDetail', ['uses' => 'Admin\AdminController@getAccountDetail']);
    Route::post('/getWareHouse', ['uses' => 'Admin\AdminController@getWareHouse']);
    Route::post('/getItemWebShop', ['uses' => 'Admin\AdminController@getItemWebShop']);
    Route::post('/addItemWebShop', ['uses' => 'Admin\AdminController@addItemWebShop']);
    Route::post('/deleteItemWebShop', ['uses' => 'Admin\AdminController@deleteItemWebShop']);
    Route::post('/updateItemWebShop', ['uses' => 'Admin\AdminController@updateItemWebShop']);

    Route::post('/updateAccount', ['uses' => 'Admin\AdminController@updateAccount']);

    Route::get('/characterList', ['uses' => 'Admin\AdminController@getCharacterList']);
    Route::post('/characterDetail', ['uses' => 'Admin\AdminController@getCharacterDetail']);
    Route::post('/updateCharacter', ['uses' => 'Admin\AdminController@updateCharacter']);

    Route::post('/viewLogs', ['uses' => 'Admin\AdminController@viewLogs']);

    Route::get('/configReset', ['uses' => 'Admin\ConfigsController@getConfigReset']);
    Route::post('/configReset', ['uses' => 'Admin\ConfigsController@postConfigReset']);

    Route::get('/configLimitReset', ['uses' => 'Admin\ConfigsController@getConfigLimitReset']);
    Route::post('/configLimitReset', ['uses' => 'Admin\ConfigsController@postConfigLimitReset']);

});