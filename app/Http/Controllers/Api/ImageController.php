<?php

namespace App\Http\Controllers\Api;

use App\Models\Image;
use Illuminate\Http\Request;
use App\Http\Requests\Api\ImageRequest;
use App\Handlers\ImageUploadHandler;
use App\TransFormers\ImageTransformer;

class ImageController extends Controller
{
    public function store(ImageRequest $request,ImageUploadHandler $uploader,Image $image){

        //获得图片对应的用户
        $user=$this->user();
        //获取图片最大宽度
        $size=$request->type=='avatar'?362:1024;
        //str_plural()可以将字符串变为复数形式 即str_plural('child',2)得到'children',这里不做改变
        $result=$uploader->save($request->image,str_plural($request->type),$user->id,$size);

        $image->path = $result['path'];
        $image->type = $request->type;
        $image->user_id = $user->id;
        $image->save();

        //返回数据 post 新资源返回201
        return $this->response->item($image,new ImageTransformer())->setStatusCode(201);
    }
}
