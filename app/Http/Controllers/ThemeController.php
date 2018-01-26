<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Model\User;
use App\Http\Model\Theme;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class ThemeController extends Controller
{
  public function add (Request $request) {
    if (!User::where('uId', $request->uId)->where('status', '>', 4)){
      return json_encode(array(
        'staus'=>0,
        'msg'=>'没有权限！'
      ));
    } else if (Theme::where('name', $request->name)->first()) {
      return json_encode(array(
        'staus'=>0,
        'msg'=>'已存在该主题！'
      ));
    } else {
      $cssText = $request->cssText;
      $name = $request->name;
      $color1 = $request->color1;
      $color2 = $request->color2;
      $file = pinyin_permalink($request->name).'.css';
      $cssText = '.header{background: '.$color1.' !important;}'.
        '.admin .view .aside{background:'.$color2.' !important;}'.
        '.admin .header{background:'.$color1.' !important;}';
      $css = Storage::disk('themes')->put($file, $cssText);
      $theme = new Theme();
      $theme->uId = md5(uniqid());
      $theme->name = $name;
      $theme->color1 = $color1;
      $theme->color2 = $color2;
      $theme->file = $file;
      $theme->author = $request->uId;
      $theme->save();
      return json_encode(array(
        'status'=>1,
        'msg'=>'主题创建成功！'
      ));
    }
  }
  public function list (Request $request) {
    // $themes = Theme::leftJoin('users', 'themes.author', '=', 'users.uId')->select('users.name as user','users.username as username', 'themes.*')->get();
    $themes = Theme::join('users', function ($join) {
      $join->on('themes.author', '=', 'users.uId');
    })->select('users.name as user','users.username as username', 'themes.*')->get();
    $user = Theme::join('users', function ($join) {
      $join->on('themes.uId', '=', 'users.theme');
    })->get()->groupBy('theme');
    foreach ($themes as $index => $val) {
      $themes[$index]['use'] = array();
    }
    foreach($user as $id => $group) {
      foreach($themes as $index => $theme) {
        if ($id === $theme->uId) {
          $themes[$index]['use'] = $group;
        }
      }
    }
    if ($request->sort) {
      $themes = json_decode(json_encode($themes), true);
      $themes = f_order($themes, 'use', $request->sort);
    }
    return json_encode(array(
      'status'=>1,
      'msg'=>'获取成功！',
      'data'=> $themes
    ));
  }
}
