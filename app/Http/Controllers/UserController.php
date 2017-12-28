<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Model\User;
use Illuminate\Support\Facades\DB;
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
                    "password"
                );
                break;
            case 'phone':
                $params = array(
                    "username",
                    "phone",
                    "password"
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
                            } else {
                                $variable->$key = $value;
                            }
                        }
                        $variable->save();
                        //注册成功，发送验证邮件
                        switch ($mode) {
                            case 'email':
                                $email = $request->$mode;
                                switch ($mode) {
                                    case 'email':
                                        $qrcode = '用户名：'. $request->username.'  邮箱：'.$request->$mode.'  密码：'.$request->password;
                                        break;
                                    case 'phone':
                                        $qrcode = '用户名：'. $request->username.'  手机号：'.$request->$mode.'  密码：'.$request->password;
                                        break;
                                }
                                $to = $request->$mode;
                                $flag = Mail::send('registermail', [
                                    'name'=>$request->username,
                                    'qrcode'=> $qrcode
                                ],function ($message) use($to) {
                                    $message->to($to)->subject('恭喜您成功注册（视觉码农）会员！');
                                });
                                return json_encode(array(
                                    'status'=> 1,
                                    'msg'=> '注册成功！',
                                    'data'=> $variable,
                                    'mail'=> $flag
                                ));
                                break;
                            case 'phone':
                                return json_encode(array(
                                    'status'=> 1,
                                    'msg'=> '注册成功！',
                                    'data'=> $variable
                                ));
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
                $request->session()->put('username', $variable->username);
                return json_encode(array(
                    'status'=> 1,
                    'msg'=> '登录成功！',
                    'data'=> $variable,
                    'online'=> $request->session()->all()
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
}
