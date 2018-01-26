<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Model\Categoryproject;
use App\Http\Model\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ProjectCategoryController extends Controller
{
  public function add (Request $request) {
    $category = new Categoryproject();
    $random = uniqid();
    $category->name = $request->name;
    $category->parent = $request->parent;
    $category->uId = md5($random);
    $category->author = $request->uId;
    $category->value = $random;
    $category->save();
    return json_encode(array(
        'status'=>1,
        'msg'=>'添加成功'
    ));
  }
  public function list (Request $request) {
    $category = Categoryproject::with('children')->first();
    return json_encode(array(
        'data'=> $category
    ));
  }
  public function del (Request $request) {
    if (!User::where('uId', $request->uId)->where('status', '>', 4)->first()){
      return json_encode(array(
        'status'=>0,
        'msg'=>'没有权限进行此项操作！'
      ));
    } else {
      $category = Categoryproject::where('uId', $request->id)->first();
      $category->delete();
      return json_encode(array(
        'status'=>1,
        'msg'=>'删除成功！'
      ));
    }
  }
}
