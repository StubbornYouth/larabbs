<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
//使用Dingo Api
$api = app('Dingo\Api\Routing\Router');

//v1版本 namespace属性是使v1版本的所有路由都指向该命名空间，方便路由书写
$api->version('v1',['namespace'=>'App\Http\Controllers\Api'], function($api) {
    //短信发送路由
    $api->post('verificationCodes','VerificationController@store')->name('api.verificationCodes.store');
    //用户注册路由
    $api->post('users','UserController@store')->name('api.users.store');
});
