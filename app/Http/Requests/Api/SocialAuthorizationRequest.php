<?php

namespace App\Http\Requests\Api;

//引用DingoApi为我们提供的基类
use Dingo\Api\Http\FormRequest;

class SocialAuthorizationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules=[
            //required_without:foo.. 只要指定字段有一个不存在，被验证的字段就必须存在且不为空
            //code授权码用来获取access_token
            'code' => 'required_without:access_token|string',
            'access_token' => 'required_without:code|string',

        ];
        //当前第三方登录为微信 且 code授权码不存在 因为openid是微信特有的
        if($this->social_type=='weixin' && !$this->code)
        {
            $rules['openid'] = 'required|string';
        }
        return $rules;
    }
}
