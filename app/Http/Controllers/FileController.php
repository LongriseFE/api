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
                            'path'=>public_path('uploads'),
                            'file'=> $filename,
                            'ext'=> $ext,
                            'size'=>$size
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
       $files = $request->base64;
       if ($files) {
            $files = explode(',', $files);
            $output_file = date('YmdHiS').uniqid().'.jpg';
            $boolean = Storage::disk('uploads')->put($output_file, base64_decode($files[1]));
            if ($boolean) {
                $data = array(
                    'path'=>null,
                    'file'=>$output_file
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
       } else {
           return json_encode(array(
               'status'=>0,
               'msg'=>'参数错误！'
           ));
       }
   }
}
