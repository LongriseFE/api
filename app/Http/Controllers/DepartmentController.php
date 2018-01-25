<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Model\Department;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Mail;

class DepartmentController extends Controller
{
    public function add (Request $request) {
        $depart = new Department();
        $random = uniqid();
        $depart->name = $request->name;
        $depart->parent = $request->parent;
        $depart->uId = md5($random);
        $depart->author = $request->uId;
        $depart->value = $random;
        $depart->save();
        return json_encode(array(
            'status'=>1,
            'msg'=>'添加成功'
        ));
    }
    public function list (Request $request) {
        // $group = Department::get()->groupBy('parent');
        // foreach($group as $id => $children) {
        //     foreach($children as $index => $item) {
        //     }
        // }
        $departments = Department::with('children')->first();
        return json_encode(array(
            'data'=> $departments
        ));
    }
}
