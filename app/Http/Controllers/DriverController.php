<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DriverController extends Controller
{
   public function makeDir (Request $request) {
       $parent = $request->parent;
       $dir = 'pan/'.$request->dir;
       $newpath = null;
       if (!$dir) {
           return json_encode(array(
               'status'=>0,
               'msg'=>'目录名称不能为空!'
           ));
       } else {
            if (!$parent) {
                // 在根目录创建
                if (Storage::exists(array_iconv($dir, 'gbk'))) {
                    return json_encode(array(
                        'status'=>0,
                        'msg'=>'该目录已存在，请更换！'
                    ));
                }
                $newpath = Storage::makeDirectory(array_iconv($dir, 'gbk'));
            } else {
                $dir = $request->dir;
                if (storage::exists(array_iconv('pan/'.$parent.'/'.$dir, 'gbk'))) {
                    return json_encode(array(
                        'status'=>0,
                        'msg'=>'该目录已存在，请更换！'
                    ));
                }
                $newpath = Storage::makeDirectory(array_iconv('pan/'.$parent.'/'.$dir, 'gbk'));
            }
            if ($newpath) {
                return json_encode(array(
                    'status'=>1,
                    'msg'=>'目录创建成功！'
                ));
            }
       }
   }
   public function getDir (Request $request) {
       $base = 'http://'.$request->server('SERVER_ADDR').'/api/'.'storage/app/';
        $dir = 'pan/'.$request->dir;
        if (Storage::exists($dir)) {
            $directions = Storage::directories($dir);
            $files = Storage::files($dir);
        } else {
            $directions = Storage::directories(array_iconv($dir, 'gbk'));
            $files = Storage::files(array_iconv($dir, 'gbk'));
        }
       foreach ($directions as $key => $val) {
           $directions[$key] = array_iconv($val, 'UTF-8');
       }
       foreach ($files as $key => $val) {
            $files[$key] = array_iconv($val, 'UTF-8');
        }
       $all = array();
       foreach ($directions as $key => $val) {
           $name = explode('/', $val);
           $name = $name[count($name) - 1];
           array_push($all, array(
               'name'=>$name,
               'dir'=>$val,
               'time'=>Storage::lastModified(array_iconv($val, 'gbk')),
               'icon'=>$base.'ext/folder.png'
           ));
       }
       foreach ($files as $key => $val) {
            $name = explode('/', $val);
            $name = $name[count($name) - 1];
            $ext = explode('.', $val);
            $ext = $ext[count($ext) - 1];
            if (Storage::exists($val)) {
                $file = $val;
            } else {
                $file = array_iconv($val, 'gbk');
            }
            array_push($all, array(
                'name'=>$name,
                'dir'=>$val,
                'time'=>Storage::lastModified(array_iconv($val, 'gbk')),
                'icon'=>$base.'ext/'.$ext.'.png'
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
   public function delDir (Request $request) {
       $dir = $request->dir;
       if (count(explode('.', $dir))>=2) {
            $group = explode(',', $dir);
            $del = array();
            foreach($group as $val) {
                array_push($del,iconv('utf-8', 'gbk', $val));
            }
            $del = Storage::delete($del);
            if ($del) {
                return json_encode(array(
                    'status'=>1,
                    'msg'=>'删除成功！',
                    'data'=> $group
                ));
            } else {
                return json_encode(array(
                    'status'=>0,
                    'msg'=>'删除失败，该路径不存在！'
                ));
            }
       } else {
            $group = explode(',', $dir);
            $del = null;
            foreach($group as $val) {
                $del = Storage::deleteDirectory(iconv('utf-8', 'gbk', $val));
            }
            if ($del) {
                return json_encode(array(
                    'status'=>1,
                    'msg'=>'删除成功！'
                ));
            } else {
                return json_encode(array(
                    'status'=>0,
                    'msg'=>'删除失败，该路径不存在！'
                ));
            }
       }
   }
   public function updateDir (Request $request) {
       $dir = 'pan/'.$request->dir;
       $value = 'pan/'.$request->value;
       $out = null;
       if (count(explode('/', $dir)) === count(explode('/', $value))) {
            if (!Storage::exists(iconv('utf-8','gbk',$dir))) {
                return json_encode(array(
                    'status'=>0,
                    'msg'=>'重命名失败，文件不存在！'
                ));
            } else if (Storage::exists(iconv('utf-8','gbk',$value))) {
                return json_encode(array(
                    'status'=>0,
                    'msg'=>'重命名失败，已存在该文件！'
                ));
            }
       } else {
            if (!Storage::exists(iconv('utf-8','gbk',$dir))) {
                return json_encode(array(
                    'status'=>0,
                    'msg'=>'移动失败，文件不存在！'
                ));
            } else if (Storage::exists(iconv('utf-8','gbk',$value))) {
                return json_encode(array(
                    'status'=>0,
                    'msg'=>'移动失败，已存在该文件！'
                ));
            }
       }
       $out = Storage::move(iconv('utf-8','gbk',$dir),iconv('utf-8','gbk',$value));
       if ($out) {
            return json_encode(array(
                'status'=>1,
                'msg'=>'重命名成功！'
            ));
       } else {
            return json_encode(array(
                'status'=>0,
                'msg'=>'重命名失败！'
            ));
       }
   }
   public function upload (Request $request) {
    if ($request->isMethod('POST')) {
        if($request->hasFile('file')) {
            $files = $request->file('file');
            if ($files->isValid()) {
                $originalName = $files->getClientOriginalName(); 
                //扩展名  
                $size = $files->getClientSize();
                $ext = $files->getClientOriginalExtension(); 
                //文件类型  
                $type = $files->getClientMimeType();
                //临时绝对路径  
                $realPath = $files->getRealPath();  
                $filename = date('YmdHiS').uniqid().'.'.$ext;  
                $bool = Storage::disk('pan')->put(iconv('utf-8','gbk',$originalName), file_get_contents($realPath));
                $path = null;
                if ($request->dir) {
                    $path = 'http://'.$request->server('SERVER_ADDR').'/api/'.'storage/app/pan'.$request->dir;
                    Storage::move(iconv('utf-8', 'gbk', 'pan/'.$originalName), iconv('utf-8', 'gbk', 'pan/'.$request->dir.'/'.$originalName));
                } else {
                    $path = 'http://'.$request->server('SERVER_ADDR').'/api/'.'storage/app/pan';
                }
                if ($bool) {
                    $data = array(
                        'path'=>$path,
                        'file'=> $filename,
                        'ext'=> $ext,
                        'size'=>getFileSize($size)
                    );
                    return json_encode(array(
                        'status'=>1,
                        'msg'=>'上传成功！',
                        'data'=>$data
                    ));
                } else {
                    return json_encode(array(
                        'status'=>0,
                        'msg'=>'上传失败，请重试！'
                    ));
                }
            } else {
                return json_encode(array(
                    'status'=>0,
                    'msg'=>'文件无效！'
                ));
            }
        } else {
            return json_encode(array(
                'status'=>0,
                'data'=>'没有file'
            ));
        }
    } else {
        return json_encode(array(
            'status'=>0,
            'msg'=>'上传文件只能是post请求！'
        ));
    }
}
   public function category (Request $request) {
       $files = Storage::allFiles('pan');
       $all = array();
       $base = 'http://'.$request->server('SERVER_ADDR').'/api/'.'storage/app/';
       foreach($files as $val) {
            $name = explode('/', $val);
            $name = iconv('gbk', 'utf-8', $name[count($name)-1]);
           array_push($all, array(
               'name'=>$name,
               'url'=> $base.iconv('gbk','utf-8', $val),
               'size'=>getFileSize(storage::size($val)),
               'ext'=>Storage::mimeType($val),
               'time'=>Storage::lastModified($val)
           ));
       }
       return json_encode(array(
           'total'=>count($all),
           'data'=>$all
       ));
   }
   public function downfile (Request $request) {
        $file = $request->url;
        $ext = explode('.', $file)[1];
        $name = $request->name;
        if (file_exists(realpath(base_path('storage/app/pan')).'\\'.$file)) {
            return response()->download(realpath(base_path('storage/app/pan')).'\\'.$file, $name.'.'.$ext);
        } else {
            return json_encode(array(
                'status'=>0,
                'msg'=>'文件不存在!'
            ));
        }
    }
}
