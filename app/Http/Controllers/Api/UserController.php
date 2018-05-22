<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\Api\UserRequest;
//引入用户转换器类
use App\TransFormers\UserTransformer;
use App\Models\Image;

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
        //return $this->response->created();

        //如果你需要注册完成后就立刻进行登录 就需要返回用户信息
        //返回信息 201状态码 post生成新资源
        //还需要token参数来进行登录 这时就需要将token放置在meta中了
        return $this->response->item($user,new UserTransformer())->setMeta(
            [
                //通过指定模型生成token
                'access_token' => \Auth::guard('api')->fromUser($user),
                //token类型
                'access_type' => 'Bearer',
                //token过期时间 60分钟
                'expires_in' => \Auth::guard('api')->factory()->getTTL() * 60,
            ]
        )->setStatusCode(201);
    }

    public function me(){
        //因为返回的是一个单一资源，使用$this->response->item
        //第一个参数是用户实例，第二个参数是刚创建的转换器
        //由于我们在父控制器中use了Dingo\Api\Routing\Helpers 这个 trait，可以直接$this->user()来获取当前登录用户
        //$this->user()等同于\Auth::guard('api')->user();
        return $this->response->item($this->user(),new UserTransformer());
    }

    public function update(UserRequest $request){
        //$this->user()等同于\Auth::guard('api')->user();获取当前登录用户
        $user=$this->user();
        //取出表单中的三个数据
        $attributes=$request->only(['name','email','introduction']);

        //如果修改了头像 这里是上传图片数据的id
        if($request->avatar_image_id){
            //取出对应的image对象
            $image=Image::find($request->avatar_image_id);
            //头像url
            $attributes['avatar']=$image->path;
        }
        //更新
        $user->update($attributes);
        //返回数据 200状态码
        return $this->response->item($user,new UserTransformer());
    }
}
