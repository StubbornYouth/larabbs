<?php
//命名空间 如果有多版本 还可以在Api文件夹下建立多个版本目录
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
//引如Result工具包的helper 这个trait用来处理接口响应
use Dingo\Api\Routing\Helpers;
use App\Http\Controllers\Controller as BaseController;

class Controller extends BaseController
{
    //trait 避免单继承的影响 代码复用 ，该类中即可使用Helpers中的方法了
    use Helpers;
}
