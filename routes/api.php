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
    //路由组 可以你在大量路由组之间共享路由属性,例如中间件和命名空间，而不需要单独定义
    $api->group([
        //Dingo Api提供的调用频率限制的中间件
        'middleware'=>'api.throttle',
        //限制该用户注册接口的调用次数 1次/分钟
        'limit' => config('api.rate_limits.sign.limit'),
        'expires' =>config('api.rate_limits.sign.expires'),
    ], function($api){
        //短信发送路由
        $api->post('verificationCodes','VerificationController@store')->name('api.verificationCodes.store');
        //用户注册路由
        $api->post('users','UserController@store')->name('api.users.store');
        //图片验证码
        $api->post('captchas','CaptchasController@store')->name('api.captchas.store');
        //第三方登录路由
        $api->post('socials/{social_type}/authorizations','AuthorizationsController@store')->name('api.socials.authorizations.store');
    });

});
