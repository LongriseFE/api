<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Model\Project;
use App\Http\Model\Comments;
use App\Http\Model\User;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
  public function add (Request $request) {
    $params = $request->all();
    $params['uId'] = md5(uniqid());
    $comments = new Comments();
    foreach($params as $key => $val) {
      $comments->$key = $val;
    }
    $result = $comments->save();
    if ($result) {
      return json_encode(array(
        'status'=>1,
        'msg'=>'评论成功！',
        'data'=> $result
      ));
    }
  }
  public function list (Request $request) {
    $pagesize = $request->pagesize;
    // $comments = Comments::with('children')->join('users', function ($join) {
    //     $join->on('comments.fromId', '=', 'users.uId');
    //   })->select('users.name as fromname', 'users.cover as fromface', 'comments.*')
    //   ->whereNull('parentId')
    //   ->paginate($pagesize);
    $comments = Comments::where('topicId', $request->topicId)->whereNull('parentId')->orderBy('created_at', 'desc')->paginate($pagesize);
    $total = count(Comments::where('topicId', $request->topicId)->get());
    foreach($comments as $index => $comment) {
      $comments[$index]['from'] = User::where('uId', $comment->fromId)->first();
      $comments[$index]['to'] = User::where('uId', $comment->toId)->first();
      $children = Comments::where('parentId', $comment->uId)->get();
      foreach($children as $idx => $child) {
        $children[$idx]['from'] = User::where('uId', $child->fromId)->first();
        $children[$idx]['to'] = User::where('uId', $child->toId)->first();
      }
      $comments[$index]['children'] = $children;
    }
    return json_encode(array(
      'status'=>1,
      'msg'=>'评论获取成功！',
      'data'=> $comments,
      'total'=> $total
    ));
  }
}
