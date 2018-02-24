<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Request
{
   public function uploadFile (Request $request) {
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
                    $bool = Storage::disk('uploads')->put($filename, file_get_contents($realPath));
                    if ($bool) {
                        $data = array(
                            'path'=>'http://'.$request->server('SERVER_ADDR').'/api/'.'storage/app/uploads',
                            'name'=> $filename,
                            'url'=> 'http://'.$request->server('SERVER_ADDR').'/api/'.'storage/app/uploads/'.$filename,
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
   public function base64 (Request $request) {
       $files = explode(' ', $request->base64);
       foreach($files as $key => $base) {
         if ($base) {
           $file = explode(',', $base);
           $output_file = date('YmdHiS').uniqid().'.jpg';
            $boolean = Storage::disk('uploads')->put($output_file, base64_decode($file[1]));
            if ($boolean) {
                $data = array(
                    'path'=>'http://'.$request->server('SERVER_ADDR').'/api/'.'storage/app/uploads',
                    'name'=>$output_file,
                    'url'=>'http://'.$request->server('SERVER_ADDR').'/api/'.'storage/app/uploads/'.$output_file
                );
                return json_encode(array(
                    'status'=>1,
                    'msg'=>'上传成功！',
                    'data'=> $data
                ));
            } else {
                return json_encode(array(
                    'status'=>0,
                    'msg'=>'上传失败，请重试！'
                ));
            }
         }
       }
   }
   // 删除文件
   public function delete (Request $request) {
        $file = $request->url;
        $group = explode(',', $file);
        foreach($group as $key => $value) {
            $del = Storage::disk('uploads')->delete($value);
        }
        if ($del) {
            return json_encode(array(
                'status'=>1,
                'msg'=>'文件删除成功!'
            ));
        } else {
            return json_encode(array(
                'status'=>0,
                'msg'=>'删除失败,文件不存在!'
            ));
        }
    }
    public function downfile (Request $request) {
        $file = $request->url;
        $ext = explode('.', $file)[1];
        $name = $request->name;
        if (file_exists(realpath(base_path('storage/app/uploads')).'/'.$file)) {
            return response()->download(realpath(base_path('storage/app/uploads')).'/'.$file, $name.'.'.$ext);
        } else {
            return json_encode(array(
                'status'=>0,
                'msg'=>'文件不存在!'
            ));
        }
    }
}
