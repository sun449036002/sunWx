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

Route::get('/', "IndexController@index")->middleware("weixinOAuth");
Route::get('/clear-all-session', "IndexController@clearAllSession");
Route::any('/oauth-callback', "Controller@oauthCallback");

Route::any('weixin/api', 'wxController@api');

Route::any("weixin/users", "wxController@users");