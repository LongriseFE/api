<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Model\Thumbs;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class ThumbsController extends Controller
{
    public function add (Request $request) {
        $thumbs = new Thumbs();
        $exist = Thumbs::where('target', $request->target)->where('user', $request->user)->first();
        if ($exist) {
            $exist->delete();
            return json_encode(array(
                'status'=>1,
                'msg'=>'已取消点赞！'
            ));
        } else {
            $thumbs->target = $request->target;
            $thumbs->user = $request->user;
            $thumbs->uId = md5(uniqid());
            $result = $thumbs->save();
            if ($result) {
                return json_encode(array(
                    'status'=>1,
                    'msg'=>'已点赞！',
                    'data'=> $result
                ));
            }
        }
    }
    public function list (Request $request) {
        $list = array();
        if ($request->target) {
            $list = Thumbs::where('target', $request->target)->join('users', 'users.uId', '=', 'thumbs.user')->select('thumbs.*', 'users.name', 'users.cover')->get();
        } else if ($request->user) {
            $list = Thumbs::where('user', $request->user)->get();
        }
        return json_encode(array(
            'status'=>1,
            'msg'=>'获取成功！',
            'data'=>$list
        ));
    }
}
