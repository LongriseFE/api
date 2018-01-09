<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Model\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
                $request->session()->put('site.username', $variable->username);
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
    public function remember (Request $request, $mode, $uId) {
        $variable = User::where('uId', $uId)->first();
        $captcha = $request->session()->get('captcha');
        switch ($mode) {
            case 'email':
                if ($request->captcha !== $captcha) {
                    return json_encode(array(
                        'status'=> 0,
                        'msg'=> '图片验证码输入有误！'
                    ));
                } else {
                    if ($request->code !== $variable->remember_token) {
                        return json_encode(array(
                            'status'=> 0,
                            'msg'=> '错误，请输入您邮箱收到的6位数字验证码！'
                        ));
                    } else {
                        $variable->password = md5($request->password);
                        $variable->update();
                        return json_encode(array(
                            'status'=> 1,
                            'msg'=> '密码修改成功，请牢记您的新密码！'
                        ));
                    }
                }
                break;
            case 'phone':
                $code = $request->session()->get('sms');
                if (intval($request->code) !== $code) {
                    return json_encode(array(
                        'status'=> 0,
                        'msg'=> '验证码输入有误，请重新输入！'
                    ));
                } else {
                    if (strlen($request->password) <6 || strlen($request->password) > 16) {
                        return json_encode(array(
                            'status'=> 0,
                            'msg'=> '新密码必须是6-16位！'
                        ));
                    } else {
                        $variable = User::where('uId', $uId)->first();
                        if (!$variable->phone) {
                            return json_encode(array(
                                'status'=> 0,
                                'msg'=> '尚未绑定任何手机号，无法通过手机号修改密码！'
                            ));
                        }else if ($variable->phone === $request->phone) {
                            $variable -> password = md5($request->password);
                            $variable->update();
                            return json_encode(array(
                                'status'=> 1,
                                'msg'=> '密码修改成功，请牢记您的新密码！'
                            ));
                        } else {
                            return json_encode(array(
                                'status'=> 0,
                                'msg'=> '手机号与账户不匹配，修改失败！'
                            ));
                        }
                    }
                }
                break;
        }
    }

    
    // 发送邮件验证码
    public function sendmail (Request $request, $uId) {
        $variable = User::where('uId', $uId)->first();
        if (!$variable->email){
            return json_encode(array(
                'status'=>0,
                'msg'=>'您尚未绑定任何邮箱，不能发送邮件！'
            ));
        } else {
            $length = 6;
            $random = rand(pow(10,($length-1)), pow(10,$length)-1);
            $to = $variable->email;
            $flag = Mail::send('remembermail', [
                'name'=>$request->username,
                'qrcode'=> $random
            ],function ($message) use($to) {
                $message->to($to)->subject('收好你的密钥（视觉码农）');
            });
            $variable->remember_token = $random;
            $variable->update();
            return json_encode(array(
                'status'=> 1,
                'msg'=> '验证码已经发送至您的邮箱，请注意查收！'
            ));
        }
    }
    public function editpassword (Request $request, $uId) {
        $variable = User::where('uId', $uId)->first();
        if (!$request->old || !$request->new || !$request->captcha) {
            return json_encode(array(
                'status'=> 0,
                'msg'=> '参数有误！'
            ));
        } else if ($request->captcha !== $request->session()->get('captcha')) {
            return json_encode(array(
                'status'=> 0,
                'msg'=> '验证码输入有误！'
            ));
        } else if (md5($request->old) !== $variable->password) {
            return json_encode(array(
                'status'=> 0,
                'msg'=> '您输入的旧密码有误！'
            ));
        } else {
            if (strlen($request->new) > 16 || strlen($request->new) < 6) {
                return json_encode(array(
                    'status'=> 0,
                    'msg'=> '您输入的新密码长度必须是6-16位！'
                ));
            } else {
                $variable->password = md5($request->new);
                $variable->update();
                return json_encode(array(
                    'status'=> 1,
                    'msg'=> '密码修改成功，请牢记您的新密码！'
                ));
            }
        }
    }
    // 个人信息二维码
    public function qrcodeinfo (Request $request, $uId) {
        $variable = User::where('uId', $uId)->first();
        return $variable;
    }
    // 删除用户（可以批量删除）
    public function destroy (Request $request, $uId) {
        $group = explode(',', $uId);
        $deleted = array();
        $notexist = array();
        forEach($group as $key => $value){
            $variable = User::where('uId', $value)->first();
            if ($variable) {
                array_push($deleted, $variable);
                $variable->delete();
            } else {
                array_push($notexist, $value);
            }
        }
        if (count($group) === 1) {
            if ($notexist) {
                return json_encode(array(
                    'status'=>0,
                    'msg'=>'该用户不存在！'
                ));
            } else {
                return json_encode(array(
                    'status'=>1,
                    'msg'=>'操作成功！',
                    'delete'=>$deleted,
                    'null'=>$notexist
                ));
            }
        } else {
            if ($notexist) {
                return json_encode(array(
                    'status'=>2,
                    'msg'=>'操作成功，您所删除的用户有不存在的！'
                ));
            } else {
                return json_encode(array(
                    'status'=>1,
                    'msg'=>'操作成功，所有用户删除成功！',
                    'delete'=>$deleted,
                    'null'=>$notexist
                ));
            }
        }
    }
    // http://localhost/api/public/update/user/aeaf0141193fa664c6079610d270111b?userInfo={%22name%22:%22%E5%88%98%E5%8B%87%22,%22sex%22:0,%22qq%22:%22979741120%22,%22status%22:%200}
    public function updateUserInfo (Request $request, $uId) {
        $userInfo = json_decode($request->userInfo);
        $variable = User::where('uId', $uId)->first();
        $protect = array("id","uId","password", "username");
        $columns = Schema::getColumnListing('users');
        $editable = array_diff($columns, $protect);
        $input = array();
        foreach($userInfo as $key => $val) {
            $variable->$key = $val;
            array_push($input, $key);
        }
        $useless = array_diff($input, $editable);
        if (!$useless) {
            $variable->update();
            return json_encode(array(
                'status'=>1,
                'msg'=>'修改成功！',
            ));
        }else {
            return json_encode(array(
                'status'=>0,
                'msg'=>'修改失败，接收参数有误！',
            ));
        }
    }
    public function updatePhone (Request $request, $uId) {

    }
}
