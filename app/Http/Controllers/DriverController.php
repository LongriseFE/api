<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DriverController extends Controller
{
   public function makeDir (Request $request) {
       $parent = $request->parent;
       $dir = iconv('utf-8','gbk',$request->dir);
       if (!$dir) {
           return json_encode(array(
               'status'=>0,
               'msg'=>'目录名称不能为空!'
           ));
       } else {
            if (!$parent) {
                // 在根目录创建
                Storage::makeDirectory('pan/'.$dir);
                $directions = Storage::directories('pan');
                $files = Storage::files('pan');
                return $files;
            } else {
                Storage::makeDirectory('pan/'.$parent.'/'.$dir);
                $directions = Storage::directories('pan'.$parent);
                $files = Storage::files('pan'.$parent);
                return $files;
            }
       }
   }
   public function getDir (Request $request) {
    //    return Storage::size(iconv('utf-8', 'gbk', 'pan/document/测试文件夹/icon5.png'));
       $base = 'http://'.$request->server('SERVER_ADDR').'/api/'.'storage/app/';
       $dir = $request->dir;
       $files = Storage::files(iconv('utf-8', 'gbk', 'pan/'.$dir));
       $directions = Storage::directories('pan/'.$dir);
       $all = array();
       foreach($directions as $key => $val) {
            $name = explode('/', $val);
            array_push($all, array(
                'name'=> iconv('gbk', 'utf-8', $name[count($name)-1]),
                'dir'=>iconv('gbk', 'utf-8', $val)
            ));
        }
       foreach($files as $key => $val) {
            $name = explode('/', $val);
            $name = iconv('gbk', 'utf-8', $name[count($name)-1]);
            $dir = iconv('gbk', 'utf-8', $val);
            $size = Storage::size($val);
            array_push($all, array(
                'name'=> $name,
                'dir'=>$dir,
                'url'=>$base.iconv('gbk', 'utf-8', $val),
                'ext'=> explode('.', $name)[1],
                'size'=>getFileSize($size)
                // 'size'=>Storage::size($dir),
                // 'lasttime'=>Storage::lastModified($dir)
            ));
       }
       return json_encode(array(
           'status'=>1,
           'msg'=>'成功获取该目录所有文件！',
           'data'=> array(
               'directions'=>count($directions),
               'files'=>count($files),
               'content'=>$all
           )
       ));
   }
}
