<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Model\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class CollectionController extends Controller
{
    public function add (Request $request) {
        $collection = new Collection();
        $exist = Collection::where('target', $request->target)->where('user', $request->user)->first();
        if ($exist) {
            $exist->delete();
            return json_encode(array(
                'status'=>2,
                'msg'=>'已取消收藏！'
            ));
        } else {
            $collection->target = $request->target;
            $collection->user = $request->user;
            $collection->uId = md5(uniqid());
            $result = $collection->save();
            if ($result) {
                return json_encode(array(
                    'status'=>1,
                    'msg'=>'已收藏！',
                    'data'=> $result
                ));
            }
        }
    }
    public function list (Request $request) {
        $list = array();
        if ($request->target) {
            $list = Collection::where('target', $request->target)->join('users', 'users.uId', '=', 'Collection.user')->select('Collection.*', 'users.name', 'users.cover')->get();
        } else if ($request->user) {
            $list = Collection::where('user', $request->user)->get();
        }
        return json_encode(array(
            'status'=>1,
            'msg'=>'获取成功！',
            'data'=>$list
        ));
    }
}
