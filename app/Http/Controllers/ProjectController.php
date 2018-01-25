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
        $between = $request->between;
        $between = json_decode($between);
        $project = Project::where(function($query) use($limit, $between){
            if (count($limit)) {
                foreach($limit as $key => $val) {
                    $query->where($key, 'like', '%'.$val.'%');
                }
            }
            if (isset($between)) {
                $query->whereBetween('created_at', [$between->start, $between->end]);
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
    public function del (Request $request) {
        $uId = $request->uId;
        $id = $request->id;
        if (!$uId) {
            return json_encode(array(
                'status'=>0,
                'msg'=>'请提供有效的用户id！'
            ));
        } else if (!User::where('uId', $uId)->first()) {
            return json_encode(array(
                'status'=>0,
                'msg'=>'不存在该用户！'
            ));
        } else if (!User::where('uId', $uId)->where('status', '>', 1)->first()) {
            return json_encode(array(
                'status'=>0,
                'msg'=>'该用户没有权限进行此操作！'
            ));
        } else {
            if (!Project::where('uId', $id)->first()){
                return json_encode(array(
                    'status'=>0,
                    'msg'=>'不存在该项目！'
                ));
            } else {
                $project = Project::where('uId', $id);
                $project->delete();
                return json_encode(array(
                    'status'=>1,
                    'msg'=>'项目删除成功！'
                ));
            }
        }
    }
    public function groups (Request $request) {
        $groupby = $request->groupby;
        if (!$groupby) {
            return json_encode(array(
                'status'=>0,
                'msg'=>'没有分组依据！'
            ));
        } else {
            $projects = Project::orderBy('updated_at',$request->order)->get()->groupBy($groupby);
            return json_encode(array(
                'status'=>1,
                'msg'=>'没有分组依据！',
                'data'=>$projects
            ));
        }
    }
}
