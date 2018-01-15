<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Model\Project;
use App\Http\Model\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProjectController extends Controller
{
    public function list (Request $request) {
        $limit = $request->limit;
        $limit = json_decode($limit);
        $param = array();
        $project = Project::where(function($query) use($limit){
            if (count($limit)) {
                foreach($limit as $key => $val) {
                    $query->where($key, $val);
                }
            }
        })->orderBy('updated_at', $request->sort)->paginate($request->pagesize);
        return json_encode(array(
            'status'=>1,
            'msg'=>'获取成功！',
            'data'=>$project
        ));
    }
    public function group (Request $request) {

    }
    public function add (Request $request) {
        $uId = $request->uId;
        if (!$uId) {
            return json_encode(array(
                'status'=>0,
                'msg'=>'非法操作，请登录后重试！'
            ));
        } else if (!count(User::where('uId', $uId)->first())) {
            return json_encode(array(
                'status'=>0,
                'msg'=>'不存在该用户，请查证后重试！'
            ));
        } else if (!count(User::where('uId', $uId)->where('status', '>', 1)->first())) {
            return json_encode(array(
                'status'=>0,
                'msg'=>'抱歉，该用用户虎没有权限进行此操作！'
            ));
        } else {
            $info = $request->projectInfo;
            $info = json_decode($info);
            if (!is_object($info)) {
                return json_encode(array(
                    'status'=>0,
                    'msg'=>'数据格式有误！'
                ));
            } else {
                $keys = array();
                $project = null;
                $fit = true;
                $projects = new Project();
                foreach($info as $key => $val) {
                    $projects->$key = $val;
                    if (Schema::hasColumn('projects', $key)) {
                        if ($key === 'uId') {
                            array_push($keys, 0);
                        } else {
                            array_push($keys, 1);
                        }
                    } else {
                        array_push($keys, 0);
                    }
                }
                foreach($keys as $val) {
                    if ($val === 0) {
                        $fit = false;
                    }
                }
                if (!$fit) {
                    return json_encode(array(
                        'status'=>0,
                        'msg'=>'未知字段名称！'
                    ));
                } else {
                    $projects->uId = md5(uniqid());
                    $result = $projects->save();
                    if ($result) {
                        return json_encode(array(
                            'status'=>1,
                            'msg'=>'发布成功！',
                            'data'=> $projects
                        ));
                    }
                }
            }
        }
    }
    public function edit (Request $request) {
        $uId = $request->uId;
        if (!$uId) {
            return json_encode(array(
                'status'=>0,
                'msg'=>'请提供项目id！'
            ));
        } else if (!count(Project::where('uId', $uId)->first())) {
            return json_encode(array(
                'status'=>0,
                'msg'=>'不存在该项目！'
            ));
        } else {
            $info = $request->projectInfo;
            $info = json_decode($info);
            if (!is_object($info)) {
                return json_encode(array(
                    'status'=>0,
                    'msg'=>'数据格式有误！'
                ));
            } else {
                $keys = array();
                $project = null;
                $fit = true;
                $projects = Project::where('uId',$uId)->first();
                foreach($info as $key => $val) {
                    $projects->$key = $val;
                    if (Schema::hasColumn('projects', $key)) {
                        if ($key === 'uId') {
                            array_push($keys, 0);
                        } else {
                            array_push($keys, 1);
                        }
                    } else {
                        array_push($keys, 0);
                    }
                }
                foreach($keys as $val) {
                    if ($val === 0) {
                        $fit = false;
                    }
                }
                if (!$fit) {
                    return json_encode(array(
                        'status'=>0,
                        'msg'=>'未知字段名称！'
                    ));
                } else {
                    $result = $projects->update();
                    if ($result) {
                        return json_encode(array(
                            'status'=>1,
                            'msg'=>'修改成功！',
                            'data'=> $projects
                        ));
                    } else {
                        return json_encode(array(
                            'status'=>0,
                            'msg'=>'修改失败！'
                        ));
                    }
                }
            }
        }
    }
}
