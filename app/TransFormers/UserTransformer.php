<?php
namespace App\Transformers;

use App\Models\User;
use League\Fractal\TransformerAbstract;

/**
 * 用户数据转换层
 */
 class UserTransformer extends TransformerAbstract
 {

     public function transform(User $user)
     {
          return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar,
            'introduction' => $user->introduction,
            'bound_phone' => $user->phone?true:false,
            'bound_wechat' => ($user->weixin_unionid || $user->weixin_openid)?true:false,
            'last_actived_at' => $user->last_actived?$user->last_actived->toDateTimeString():'暂无',
            'created_at' => $user->created_at->toDateTimeString(),
            'updated_at' => $user->updated_at->toDateTimeString(),
          ];
     }
 }