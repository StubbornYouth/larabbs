<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\Api\SocialAuthorizationRequest;
use App\Http\Requests\Api\AuthorizationRequest;

class AuthorizationsController extends Controller
{
    //第三方登录(权限码获取access_token和openid以及access_token和openid获取用户信息集合)
    public function socialStore($type,SocialAuthorizationRequest $request){
        if (!in_array($type, ['weixin'])) {
            //返回错误
            return $this->response->errorBadRequest();
        }
        //认证页面 $type即第三方
        $driver = \Socialite::driver($type);

        try {
            //判断是否是权限码获取access_token
            if ($code = $request->code) {
                    //利用权限码到第三方获取返回数据
                    $response = $driver->getAccessTokenResponse($code);
                    //从数组数据中取出access_token参数
                    $token = array_get($response, 'access_token');
            } else {
                //若是利用access_token获取用户信息
                //得到请求的access_token
                $token = $request->access_token;
                //如果第三方是微信
                if ($type == 'weixin') {
                    //设置认证所需的openid
                    $driver->setOpenId($request->openid);
                }
            }
            //通过access_token获取用户信息
            $oauthUser = $driver->userFromToken($token);
        } catch (\Exception $e) {
            //若参数都不符合要求 报错
            //返回401错误 认证失败
            return $this->response->errorUnauthorized('参数错误，未获取用户信息');
        }

        switch ($type) {
            //第三方是微信
            case 'weixin':
                //得到unionid
                $unionid = $oauthUser->offsetExists('unionid') ? $oauthUser->offsetGet('unionid') : null;

                if ($unionid) {
                    //取得数据库中与unionid相同的用户(可能注册同一个开发者下的其它应用)
                    $user = User::where('weixin_unionid', $unionid)->first();
                } else {
                    //取得数据库中与openid相同的用户(可能之前已经登陆过了)
                    $user = User::where('weixin_openid', $oauthUser->getId())->first();
                }

                // 没有用户，默认创建一个用户
                if (!$user) {
                    $user = User::create([
                        'name' => $oauthUser->getNickname(),
                        'avatar' => $oauthUser->getAvatar(),
                        'weixin_openid' => $oauthUser->getId(),
                        'weixin_unionid' => $unionid,
                    ]);
                }

                break;
        }
        //通过用户模型生成token
        $token=\Auth::guard('api')->formUser($user);

        //返回响应信息 用户id
        return $this->respondWithToken($token)->setStatusCode(201);
    }
    //普通登录
    public function store(AuthorizationRequest $request){
        $username=$request->username;
        //检查是否存在指定类型的变量
        //验证username是不是邮箱
        //如果是邮箱的话就是邮箱账号，不是的话就是手机账号
        filter_var($username,FILTER_VALIDATE_EMAIL)?$credentials['email']=$username:$credentials['phone']=$username;
         $credentials['password'] = $request->password;
        //使用我们在auth.php中配置的api jwt去获取token
         //匹配数据库账号密码生成token
        if(!$token=\Auth::guard('api')->attempt($credentials)){
            //返回401错误
            return $this->response->errorUnauthorized('用户名或密码错误');
        }

        //返回响应信息
        return $this->respondWithToken($token)->setStatusCode(201);
    }


    protected function respondWithToken($token){
        //返回响应信息
         return $this->response->array([
            //token
            'access_token' => $token,
            'token_type' => 'Bearer',
            //过期时间
            'expires_in' => \Auth::guard('api')->factory()->getTTL() * 60,
        ]);
    }
    //更新用户access_token 登录授权
    public function update(){
        $token=\Auth::guard('api')->refresh();
        return $this->respondWithToken($token);
    }

    //删除用户登录授权 access_token
    public function destroy(){
        \Auth::guard('api')->logout();
        //返回204状态码 响应成功,当不需要返回信息
        return $this->response->noContent();
    }
}
