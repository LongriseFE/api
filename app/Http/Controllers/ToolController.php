<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Gregwar\Captcha\CaptchaBuilder;
use App\Http\Model\User;
use Illuminate\Support\Facades\Cache;
use Mail;

class ToolController extends Controller
{
   // 图片验证码
   public function captcha (Request $request) {
        $builder = new CaptchaBuilder;
        //可以设置图片宽高及字体
        $builder->build($width = 120, $height = 40, $font = null);
        //获取验证码的内容
        $phrase = $builder->getPhrase();

        //把内容存入session
        // $request->session()->put('captcha', $phrase);
        Cache::put('captcha', $phrase, 30);
        //生成图片
        header("Cache-Control: no-cache, must-revalidate");
        header('Content-Type: image/jpeg');
        $builder->output();
    }
    public function getcaptcha (Request $request) {
        $result = Cache::get('captcha');
        if ($result) {
            return json_encode(array(
                'status'=>1,
                'msg'=>'获取成功！',
                'data'=> $result
            ));
        } else {
            return json_encode(array(
                'status'=>0,
                'msg'=>'请刷新后重试！'
            ));
        }
    }
    public function mail (Request $request) {
        $email = $request->email;
        $title = $request->title;
        $content = $request->content;
        $file = $request->file;
        $flag = Mail::send('mail', [
            'content'=>$content
        ],function ($message) use($email, $title, $content,$file) {
            $message->to($email)->subject($title);
            if ($file) {
                $message->attach($file);
            }
        });
        return json_encode(array(
            'status'=> 1,
            'msg'=> '验证码已经发送至您的邮箱，请注意查收！'
        ));
    }
    // 发送短信验证码接口
    // http://localhost/api/public/sms?type=0&captcha=8j8m1&phone=18827078587
    public function sms (Request $request) {
        $type = $request->type;
        $captcha = Cache::get('captcha');
        if (!$request->captcha) {
            return json_encode(array(
                'status'=> 0,
                'msg'=> '短信验证码发送失败，验证码为空！'
            ));
        } else if ($request->captcha !== $captcha) {
            return json_encode(array(
                'status'=> 0,
                'msg'=> '图片验证码输入有误！'
            ));
        } else if (!preg_match('/^1[34578]{1}\d{9}$/', $request->phone)) {
            return json_encode(array(
                'status'=> 0,
                'msg'=> '手机号格式有误！'
            ));
        } else {
            $length = 6;
            $code = rand(pow(10,($length-1)), pow(10,$length)-1); //随机生成的6为数字验证码
            Cache::put('sms', $code, 10);
            $params = array(
                'mobile' => $request->phone,
                'content' => '【视觉码农】：您本次的验证码为：'.$code.'，请勿泄露该验证码！',
                // 'appkey' => '3337a274860d078331853490716ce6e6',
                'appkey' => 'b67d0ca28133cfd5fd337f2f36f4249e'
            );
            $url = 'https://way.jd.com/chuangxin/dxjk';
            $result = null;
            $variable = User::where('phone', $request->phone)->first();
            switch ($type) {
                case '0':
                    // 注册模式，需判断是否存在该用户
                    if ($variable) {
                        return json_encode(array(
                            'status'=>0,
                            'msg'=>'该手机号已经注册！'
                        ));
                    } else {
                        // 如果没有注册就发送验证码
                        Cache::put('phone', $request->phone, 10);
                        $result = wx_http_request($url, $params );
                    }
                    break;
                case '1':
                    // 忘记密码
                    if (!$variable) {
                        return json_encode(array(
                            'status'=>0,
                            'msg'=>'不存在该手机用户，无法发送验证码！'
                        ));
                    } else {
                        // 如果没有注册就发送验证码
                        $result = wx_http_request($url, $params );
                    }
                    break;
                case '2':
                    // 更改手机号
                    if ($variable) {
                        return json_encode(array(
                            'status'=>0,
                            'msg'=>'该手机号已被注册，请更换手机号！'
                        ));
                    } else {
                        // 如果没有注册就发送验证码
                        $result = wx_http_request($url, $params );
                    }
                    break;
            }
            // 返回发送短信结果！
            if (!$result) {
                return json_encode(array(
                    'status'=>0,
                    'msg'=>'验证码发送失败，请重试！',
                    'code'=>Cache::get('sms')
                ));
            }
            switch (json_decode($result)->code) {
                case '10000':
                    return json_encode(array(
                        'status'=>1,
                        'msg'=>'验证码发送成功！',
                        'code'=>Cache::get('sms')
                    ));
                    break;
                case '10001':
                    return json_encode(array(
                        'status'=>0,
                        'msg'=>'错误的请求appkey！',
                        'code'=>$request->session()->get('sms')
                    ));
                    break;
                case '11010':
                    return json_encode(array(
                        'status'=>0,
                        'msg'=>'商家接口调用异常，请稍后再试'
                    ));
                    break;
                case '11030':
                    return json_encode(array(
                        'status'=>0,
                        'msg'=>'商家接口返回格式有误'
                    ));
                    break;
                case '10003':
                    return json_encode(array(
                        'status'=>0,
                        'msg'=>'不存在相应的数据信息'
                    ));
                    break;
                case '10004':
                    return json_encode(array(
                        'status'=>0,
                        'msg'=>'URL上appkey参数不能为空'
                    ));
                    break;
                case '10010':
                    return json_encode(array(
                        'status'=>1,
                        'msg'=>'接口需要付费，请充值',
                        'code'=>Cache::get('sms')
                    ));
                    break;
                case '10020':
                    return json_encode(array(
                        'status'=>0,
                        'msg'=>'万象系统繁忙，请稍后再试'
                    ));
                    break;
                case '10030':
                    return json_encode(array(
                        'status'=>0,
                        'msg'=>'调用万象网关失败， 请与万象联系'
                    ));
                    break;
                case '10040':
                    return json_encode(array(
                        'status'=>0,
                        'msg'=>'超过每天限量，请明天继续'
                    ));
                    break;
                case '10050':
                    return json_encode(array(
                        'status'=>0,
                        'msg'=>'用户已被禁用'
                    ));
                    break;
                case '10060':
                    return json_encode(array(
                        'status'=>0,
                        'msg'=>'提供方设置调用权限，请联系提供方'
                    ));
                    break;
                case '10070':
                    return json_encode(array(
                        'status'=>0,
                        'msg'=>'该数据只允许企业用户调用'
                    ));
                    break;
                case '10090':
                    return json_encode(array(
                        'status'=>0,
                        'msg'=>'文件大小超限，请上传小于1M的文件'
                    ));
                    break;
            }
        }
    }
}
