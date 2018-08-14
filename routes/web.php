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
//首页
Route::get('/', "IndexController@home")->middleware("weixinOAuth");

//房源列表 详细 ajax列表
Route::get('/room/list', "RoomController@index")->middleware("weixinOAuth");
Route::get('/room/getRoomList', "RoomController@getRoomList")->middleware("weixinOAuth");
Route::get('/room/detail', "RoomController@detail")->middleware("weixinOAuth");

//地域列表
Route::get('/area/list', "RoomController@getAreaList")->middleware("weixinOAuth");
Route::get('/houseType/list', "RoomController@getHouseTypeList")->middleware("weixinOAuth");
Route::get('/category/list', "RoomController@getCategoryList")->middleware("weixinOAuth");


//我的个人中心
Route::get('/my', "MyController@index")->middleware("weixinOAuth");

//领现金红包
Route::get('cash-red-pack', "IndexController@cashRedPack")->middleware("weixinOAuth");
Route::get('cash-red-pack-info', "IndexController@cashRedPackInfo")->middleware("weixinOAuth");
Route::get('assistance-page', "IndexController@assistancePage")->middleware("weixinOAuth");
Route::post('assistance', "IndexController@assistance")->middleware("weixinOAuth");


/**
 * 微信相关
 */
Route::any('weixin/api', 'wxController@api');
Route::any("weixin/users", "wxController@users");
Route::any("weixin/qrCode", "wxController@getQrCode")->middleware("weixinOAuth");


//网页授权回调
Route::any('/oauth-callback', "Controller@oauthCallback");
Route::get('/cc', "IndexController@clearCookie");
Route::get('/gc', "IndexController@getCookie");
Route::get('/debug', "IndexController@debug");

