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
//签到领现金
Route::get('/', "IndexController@index")->middleware("weixinOAuth");

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

//网页授权回调
Route::any('/oauth-callback', "Controller@oauthCallback");
Route::any('/cc', "IndexController@clearCookie");

