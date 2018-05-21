<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\Api\UserRequest;
//引入用户转换器类
use App\TransFormers\UserTransformer;

class UserController extends Controller
{
    public function store(UserRequest $request){
        //获取验证码缓存信息
        $verifyData=\Cache::get($request->verification_key);

        //如果信息为空
        if(!$verifyData){
            //422为校样错误状态码
            return $this->response->error('验证码已失效',422);
        }
        //比较两个字符串，无论它们是否相等，本函数的时间消耗是恒定的。

        //本函数可以用在需要防止时序攻击的字符串比较场景中， 例如，可以用在比较 crypt() 密码哈希值的场景。
        //判断短信验证码是否相同
        if(!hash_equals($verifyData['code'],$request->verification_code))
        {
            //返回401错误码 没有进行认证或者认证非法
            return $this->response->errorUnauthorized('验证码错误');
        }

        //成功即创建用户
        $user=User::create([
            'name' => $request->name,
            'phone' => $verifyData['phone'],
            'password' => bcrypt($request->password),
        ]);

        //清除指定缓存
        \Cache::forget($request->verification_key);

        //通过 DingoApi 提供的 created 方法返回，状态码为 201
        return $this->response->created();
    }

    public function me(){
        //因为返回的是一个单一资源，使用$this->response->item
        //第一个参数是用户实例，第二个参数是刚创建的转换器
        //由于我们在父控制器中use了Dingo\Api\Routing\Helpers 这个 trait，可以直接$this->user()来获取当前登录用户
        //$this->user()等同于\Auth::guard('api')->user()
        return $this->response->item($this->user(),new UserTransformer);
    }
}
