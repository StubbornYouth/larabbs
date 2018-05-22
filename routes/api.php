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
//添加中间件，参数为array,这可以使我们的数据结构变成ArraySerializer,少一层data嵌套
//这个Dingo Serializer Switch中间件用来切换数据结构 serializer 和 serializer:data_array 就是使用DataArraySerializer,多一层data嵌套
$api->version('v1',['namespace'=>'App\Http\Controllers\Api','middleware'=>'serializer:array'], function($api) {
    //路由组 可以你在大量路由组之间共享路由属性,例如中间件和命名空间，而不需要单独定义
    $api->group([
        //Dingo Api提供的调用频率限制的中间件
        'middleware'=>'api.throttle',
        //限制该用户注册接口的调用次数 1次/分钟
        'limit' => config('api.rate_limits.sign.limit'),
        'expires' =>config('api.rate_limits.sign.expires'),
    ], function($api){
        //游客能够访问的接口
        //短信发送路由
        $api->post('verificationCodes','VerificationController@store')->name('api.verificationCodes.store');
        //用户注册路由
        $api->post('users','UserController@store')->name('api.users.store');
        //图片验证码
        $api->post('captchas','CaptchasController@store')->name('api.captchas.store');
        //第三方登录路由
        $api->post('socials/{social_type}/authorizations','AuthorizationsController@socialStore')->name('api.socials.authorizations.store');
        //用户普通登录
        $api->post('authorizations','AuthorizationsController@Store')->name('api.authorizations.store');
        //替换当前用户登录授权凭证 access_token路由
        $api->put('authorizations/current','AuthorizationsController@update')->name('api.authorizations.update');
        //删除当前用户登录授权凭证
        $api->delete('authorizations/current','AuthorizationsController@destroy')->name('api.authorizations.destroy');

        //需要token验证的接口
        $api->group(['middleware'=>'api.auth'],function($api){
            $api->get('user','UserController@me')->name('api.user.show');
            //图片资源接口
            $api->post('images','ImageController@store')->name('api.image.store');
            //编辑用户资料
            //patch 与 put 区别:put是替换一个资源 需上传全部信息
            //patch 是替换部分资源，只需要上传部分信息
            $api->patch('users','UserController@update')->name('api.image.update');
        });

    });

});
