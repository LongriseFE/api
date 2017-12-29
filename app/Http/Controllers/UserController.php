<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Model\User;
use Illuminate\Support\Facades\DB;
use Gregwar\Captcha\CaptchaBuilder;
use Mail;

class UserController extends Controller
{
    // 用户注册
    public function register (Request $request, $mode) {
        $variable = new User();
        $params = null;
        $req = $request -> all();
        $receive = array();
        $correct = false;
        switch ($mode) {
            case 'email':
                $params = array(
                    "username",
                    "email",
                    "password",
                    "captcha"
                );
                break;
            case 'phone':
                $params = array(
                    "username",
                    "phone",
                    "password",
                    "code"
                );
                break;
        }
        foreach($req as $key => $value) {
            array_push($receive, $key);
        }
        $correct = count(array_diff($params, $receive)) === 0;
        if ($correct) {
            // params验证通过
            switch ($mode) {
                case 'phone':
                    $preg = preg_match("/^1[34578]{1}\d{9}$/", $request->$mode);
                    break;
                case 'email':
                    $preg = preg_match('/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/', $request->$mode);
                    break;
            }
            if ($preg) {
                // 邮箱或者手机格式验证通过
                if (strlen($request->password) > 16 || strlen($request->password) < 6) {
                    return json_encode(array(
                        'status'=> 0,
                        'msg'=> '密码长度必须是6-16位！'
                    ));
                } else {
                    // 开始注册
                    if (count($variable::where('username', $request->username)->get())) {
                        return json_encode(array(
                            'status'=> 0,
                            'msg'=> '该用户名已被注册！'
                        ));
                    } else if(count($variable::where($mode, $request->$mode)->get())){
                        return json_encode(array(
                            'status'=> 0,
                            'msg'=> $request->$mode.'已被占用！'
                        ));
                    } else {
                        $un = md5(uniqid());
                        $variable->uId = $un;
                        foreach($req as $key => $value) {
                            if ($key === 'password') {
                                $variable->$key=md5($value);
                            } else if ($key !== 'code' && $key !== 'captcha') {
                                $variable->$key = $value;
                            }
                        }
                        //注册成功，发送验证邮件
                        switch ($mode) {
                            case 'email':
                                if ($request->captcha !== $request->session()->get('captcha')) {
                                    return json_encode(array(
                                        'status'=> 0,
                                        'msg'=> '验证码输入有误！'
                                    ));
                                } else {
                                    $email = $request->$mode;
                                    $qrcode = '用户名：'. $request->username.'  邮箱：'.$request->$mode.'  密码：'.$request->password;
                                    $to = $request->$mode;
                                    $flag = Mail::send('registermail', [
                                        'name'=>$request->username,
                                        'qrcode'=> $qrcode
                                    ],function ($message) use($to) {
                                        $message->to($to)->subject('恭喜您成功注册（视觉码农）会员！');
                                    });
                                    $variable->save();
                                    return json_encode(array(
                                        'status'=> 1,
                                        'msg'=> '注册成功！',
                                        'data'=> $variable,
                                        'mail'=> $flag
                                    ));
                                }
                                break;
                            case 'phone':
                                if (strlen($request->code) !== 6) {
                                    return json_encode(array(
                                        'status'=> 0,
                                        'msg'=> '验证码格式有误！'
                                    ));
                                } else {
                                    $original = $request->session()->get('sms');
                                    if ($original === intval($request->code)) {
                                        $variable->save();
                                        return json_encode(array(
                                            'status'=> 1,
                                            'msg'=> '注册成功！',
                                            'data'=> $variable
                                        ));
                                    } else {
                                        return json_encode(array(
                                            'status'=> 0,
                                            'msg'=> '验证码输入有误，请重试！'
                                        ));
                                    }
                                }
                                break;
                        }
                    }
                }
            } else {
                return json_encode(array(
                    'status'=> 0,
                    'msg'=> $mode.'格式有误！'
                ));
            }
            
        } else {
            return json_encode(array(
                'status'=> 0,
                'msg'=> '通过'.$mode.'注册参数不正确，正确格式如下！',
                'params'=>$params
            ));
        }
    }
    // 用户登录
    public function login (Request $request) {
        $req = $request->all();
        $recieve = array();
        $params = array(
            'username',
            'email',
            'phone',
            'password'
        );
        foreach($req as $key => $value) {
            array_push($recieve, $key);
        }
        if (count($req) > 2) {
            return json_encode(array(
                'status'=> 0,
                'msg'=> '参数错误！'
            ));
        } else if (count(array_diff($params, $recieve)) === 2) {
            $variable = null;
            if ($request->username) {
                if (User::where('username', $request->username)->first()) {
                    $variable = User::where('username', $request->username)->where('password', md5($request->password))->first();
                } else {
                    return json_encode(array(
                        'status'=> 0,
                        'msg'=> '登录失败，不存在该用户名，请检查后重试！'
                    ));
                }
            } else if ($request->email) {
                if (preg_match('/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/', $request->email)) {
                    if (User::where('email', $request->email)->first()) {
                        $variable = User::where('email', $request->email)->where('password', md5($request->password))->first();
                    } else {
                        return json_encode(array(
                            'status'=> 0,
                            'msg'=> '登录失败，该邮箱尚未注册本站会员，请检查后重试！'
                        ));
                    }
                } else {
                    return json_encode(array(
                        'status'=> 0,
                        'msg'=> '登录失败，邮箱格式有误！'
                    ));
                }
            } else if ($request->phone) {
                if (preg_match('/^1[34578]{1}\d{9}$/', $request->phone)) {
                    if (User::where('phone', $request->phone)->first()) {
                        $variable = User::where('phone', $request->phone)->where('password', md5($request->password))->first();
                    } else {
                        return json_encode(array(
                            'status'=> 0,
                            'msg'=> '登录失败，该手机尚未注册本站会员，请检查后重试！'
                        ));
                    }
                } else {
                    return json_encode(array(
                        'status'=> 0,
                        'msg'=> '登录失败，手机号格式有误！'
                    ));
                }
            }
            if ($variable) {
                $request->session()->push('site.username', $variable->username);
                return json_encode(array(
                    'status'=> 1,
                    'msg'=> '登录成功！',
                    'data'=> $variable,
                    'online'=> $request->session()->get('site')
                ));
            }
        } else {
            return json_encode(array(
                'status'=> 0,
                'msg'=> '参数错误！',
                'content'=>'username、email、phone三者任选其一，password为必须！'
            ));
        }
    }
    // 在线人数统计
    public function online (Request $request) {
        if ($request->session()->has('site')) {
            $site = $request->session()->get('site');
            dd($site);   
        }
    }
    // 忘记密码
    public function remember (Request $request, $mode, $uId, $code) {
        $variable = User::where('uId', $uId)->first();
        switch ($mode) {
            case 'email':
                $length = 6;
                $code = rand(pow(10,($length-1)), pow(10,$length)-1);
                $to = $variable->$mode;
                $flag = Mail::send('remembermail', [
                    'name'=>$request->username,
                    'qrcode'=> $code
                ],function ($message) use($to) {
                    $message->to($to)->subject('收好你的密钥（视觉码农）');
                });
                $variable->remember_token = $code;
                $variable->update();
                return json_encode(array(
                    'status'=> 1,
                    'msg'=> '操作成功，验证码已经发送至您的邮箱，请查收！',
                    'password'=>$variable->password
                ));
                break;
            case 'phone':
                return json_encode(array(
                    'code'=>$request->session()->get('sms')
                ));
                break;
        }
    }

    // 发送短信验证码接口
    public function sms (Request $request) {
        $type = $request->type;
        $captcha = $request->session()->get('captcha');
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
            $request->session()->put('sms', $code);
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
                    'code'=>$request->session()->get('sms')
                ));
            }
            switch (json_decode($result)->code) {
                case '10000':
                    return json_encode(array(
                        'status'=>1,
                        'msg'=>'验证码发送成功！',
                        'code'=>$request->session()->get('sms')
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
                        'status'=>0,
                        'msg'=>'接口需要付费，请充值',
                        'code'=>$request->session()->get('sms')
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
    public function captcha (Request $request) {
        $builder = new CaptchaBuilder;
        //可以设置图片宽高及字体
        $builder->build($width = 140, $height = 60, $font = null);
        //获取验证码的内容
        $phrase = $builder->getPhrase();

        //把内容存入session
        $request->session()->put('captcha', $phrase);
        //生成图片
        header("Cache-Control: no-cache, must-revalidate");
        header('Content-Type: image/jpeg');
        $builder->output();
    }
    public function checkcaptcha(Request $request) {
        return $request->session()->get('captcha');
    }
}
