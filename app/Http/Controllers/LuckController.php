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
    public function list (Request $request) {
        $area = $request->area;
        $grade = $request->grade;
        $year = $request->year;
        $pagesize = $request->pagesize;
        $prize = Prize::where('year', $year)->where('grade', $grade)->where('area', $area)->first();
        $selected = Luck::where('year', $year)->where('area', $area)->where('grade', $grade)->get();
        if (!$prize) {
            return json_encode(array(
                'status'=>0,
                'msg'=>'不存在该奖项设置！'
            ));
        } else if (count($selected) >= $prize->total) {
            return json_encode(array(
                'status'=>0,
                'msg'=>'该奖项已经抽完！'
            ));
        } else if (intval($pagesize) > $prize->balance) {
            return json_encode(array(
                'status'=>0,
                'msg'=>'抽奖人数超过奖项设置人数！'
            ));
        } else {
            $users = array();
            if (Cache::get('users')) {
                $users = Cache::get('users');
            } else {
                $users = User::where('living', 'like', '%'.$area.'%')->get();
            }
            $users = json_decode(json_encode($users), true);
            $luck = array_rand($users, intval($pagesize));
            $luckerInfo = array();
            foreach ($luck as $k => $index) {
                foreach($users as $key => $val) {
                    if ($key === $index) {
                        array_push($luckerInfo, $val);
                        $lucker = new Luck();
                        $lucker->uId = md5(uniqid());
                        $lucker->ider = $val['uId'];
                        $lucker->name = $val['name'];
                        $lucker->face = $val['cover'];
                        $lucker->sex = $val['sex'];
                        $lucker->area = $area;
                        $lucker->year = $year;
                        $lucker->grade = $grade;
                        $lucker->gift = $prize->gift;
                        $lucker->save();
                    }
                }
                unset($users[$index]);
            }
            $prize->balance = $prize->balance - $pagesize;
            $prize->update();
            Cache::set('users', $users, 10);
            return json_encode(array(
                'length'=>count($users),
                'luck'=>$luckerInfo
            ));
        }
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
