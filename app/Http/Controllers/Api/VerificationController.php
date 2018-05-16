<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
//引入短信发送类
use Overtrue\EasySms\EasySms;
//引入手机短信表单验证请求
use App\Http\Requests\Api\VerificationCodeRequest;

//继承当前命名空间的Controller
class VerificationController extends Controller
{
    //
    public function store(VerificationCodeRequest $request,EasySms $easySms){
        //取出图片验证码的缓存
        $captchaData=\Cache::get($request->captcha_key);
        //判断是否为空
        if(!$captchaData){
            //返回422校样错误
            return $this->response->error('图片验证码已失效',422);
        }
        //判断验证码是否相同
        if(!hash_equals($captchaData['code'],$request->captcha_code)){
            //验证错就清除缓存
            \Cache::forget($request->captcha_key);
            //返回422校样错误
            return $this->response->erroeUnauthorized('验证码错误');
        }
        //返回响应 获得缓存中的手机号数据
        $phone=$captchaData['phone'];
        //如果当前不是生产环境 默认不发送真实短信
        if (!app()->environment('production')) {
            $code='123456';
        } else {
        //验证码生成 随机生成六位随机数,左侧补零
        $code=str_pad(random_int(1,999999),6,0,STR_PAD_LEFT);
        //短信发送
        try {
            $result = $easySms->send($phone, [
                'content'  =>  "【敲悄喬】您的验证码是{$code}。如非本人操作，请忽略本短信"
            ]);
        } catch (\GuzzleHttp\Exception\ClientException $exception) {
            $response = $exception->getResponse();
            $result = json_decode($response->getBody()->getContents(), true);
            return $this->response->errorInternal($result['msg'] ?? '短信发送异常');
        }
        }
        //随机生成一个key
        $key='verificationCode_'.str_random(15);
        //缓存过期时间设置为10分钟
        $expiredAt=now()->addMinutes(10);
        //把验证码和手机号写入缓存 第一个为对应键 第二个为值 第三个为有效时间
        \Cache::put($key,['phone'=>$phone,'code'=>$code],$expiredAt);

        //清除图片验证码缓存
        \Cache::forget($request->captcha_key);
        //返回服务器响应信息 使用的是Dingo中的helper trait
        return $this->response->array([
            'key' => $key,
            //把时间转成字符串
            'expired_at' =>  $expiredAt->toDateTimeString(),
            //setStatusCode() 设置状态码为201
        ])->setStatusCode(201);;
    }
}
