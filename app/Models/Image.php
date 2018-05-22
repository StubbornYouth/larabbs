<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    //
    protected $table='images';

    protected $fillable=['type','path'];

    //图片属于某个用户
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
