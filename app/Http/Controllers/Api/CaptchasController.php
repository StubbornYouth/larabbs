<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
//利用该类来生成验证码图片
use Gregwar\Captcha\CaptchaBuilder;
use App\Http\Requests\Api\CaptchasRequest;

class CaptchasController extends Controller
{
    public function store(CaptchasRequest $request, CaptchaBuilder $captchaBuilder)
    {
        //验证码key
        $key = 'captcha-'.str_random(15);
        //手机号
        $phone = $request->phone;
        //创建验证码
        $capcha = $captchaBuilder->build();
        //缓存过期时间
        $expiredAt = now()->addMinutes(2);
        //存入缓存 getPhrase() 获得验证码短语 用来用户测试判断
        \Cache::put($key,['phone'=>$phone,'code'=>$capcha->getPhrase()],$expiredAt);

        $result=[
            'captcha_key' => $key,
            'expire_at' => $expiredAt->toDateTimeString(),
            //验证码图片路径
            'captcha_image_content' =>$capcha->inline(),
        ];

        //服务器响应
        return $this->response->array($result)->setStatusCode(201);
    }
}
