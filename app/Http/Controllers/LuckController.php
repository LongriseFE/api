<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Model\User;
use App\Http\Model\Prize;
use App\Http\Model\Luck;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Mail;

class LuckController extends Controller
{
    public function getUser (Request $request) {
        $user = User::where('living', 'like', '%'.$request->area.'%')->get();
        return json_encode(array(
            'status'=> 1,
            'msg'=>'获取成功！',
            'total'=>count($user),
            'data'=>$user
        ));
    }
    public function reset (Request $request) {
        $users = User::where('living', 'like', '%'.$request->area.'%')->get();
        Cache::set('users', $users, 10);
        $luckers = null;
        if (intval($request->grade) === -1) {
            $luckers = Luck::where('year', $request->year)->where('area', $request->area)->delete();
            $prize = Prize::where('year', $request->year)->where('area', $request->area)->get();
            $id = array();
            foreach($prize as $key => $val) {
                array_push($id, $val->uId);
            }
            foreach($id as $val) {
                $prizes = Prize::where('uId', $val)->first();
                $prizes->balance = $prizes['total'];
                $prizes->update();
            }
        } else {
            $luckers = Luck::where('year', $request->year)->where('area', $request->area)->where('grade', $request->grade)
            ->delete();
            $prize = Prize::where('year', $request->year)->where('area', $request->area)->where('grade', $request->grade)->get();
            $id = array();
            foreach($prize as $key => $val) {
                array_push($id, $val->uId);
            }
            foreach($id as $val) {
                $prizes = Prize::where('uId', $val)->first();
                $prizes->balance = $prizes['total'];
                $prizes->update();
            }
        }

        return json_encode(array(
            'status'=>1,
            'msg'=>'重置成功！',
            'count'=>count($users),
            'data'=>$users
        ));
    }
    public function add (Request $request) {
        if (!$request->year) {
            return json_encode(array(
                'status'=>0,
                'msg'=> '缺少年份信息！'
            ));
        } else if (!$request->area) {
            return json_encode(array(
                'status'=>0,
                'msg'=> '缺少地域信息！'
            ));
        } else if ($request->grade === '' || !$request->name) {
            return json_encode(array(
                'status'=>0,
                'msg'=> '缺少奖项名称！'
            ));
        } else if (!$request->gift) {
            return json_encode(array(
                'status'=>0,
                'msg'=> '缺少奖品信息！'
            ));
        } else if (!$request->total) {
            return json_encode(array(
                'status'=>0,
                'msg'=> '缺少奖项个数信息！'
            ));
        } else {
            $prize = new Prize();
            foreach($request->all() as $key =>$val) {
                $prize->$key = $val;
            }
            $prize->uId = md5(uniqid());
            $prize->balance = $request->total;
            $prize->save();
            return json_encode(array(
                'status'=>1,
                'msg'=> '成功添加奖项！'
            ));
        }
    }
    public function prize (Request $request) {
        $area = $request->area;
        $grade = $request->grade;
        $year = $request->year;
        $prize = Prize::where('year', $year)->where('grade', $grade)->where('area', $area)->first();
        return json_encode(array(
            'data'=> $prize
        ));
    }
}
