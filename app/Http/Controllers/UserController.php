<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Model\User;
use App\Http\Model\Message;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Mail;

class UserController extends Controller
{
    // 用户注册
    public function register (Request $request) {
        $variable = new User();
        $msg = new Message();
        $params = null;
        $req = $request -> all();
        $receive = array();
        $correct = false;
        $mode = null;
        if ($request->email && !$request->phone) {
            $params = array(
                "username",
                "email",
                "password",
                "captcha",
                "code"
            );
            $mode = 'email';
        } else if (!$request->email && $request->phone) {
            $params = array(
                "username",
                "phone",
                "password",
                "code"
            );
            $mode = 'phone';
        } else {
            return json_encode(array(
                'status'=> 0,
                'msg'=> '参数有误！'
            ));
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
                if (strlen($request->username) > 16 || strlen($request->username) < 6) {
                    return json_encode(array(
                        'status'=> 0,
                        'msg'=> '用户名长度必须是6-16位！'
                    ));
                } else if (strlen($request->password) > 16 || strlen($request->password) < 6) {
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
                                if ($request->captcha !== Cache::get('captcha')) {
                                    return json_encode(array(
                                        'status'=> 0,
                                        'msg'=> '请填写图片验证码！'
                                    ));
                                } else if (intval($request->code) !== intval(Cache::get('code'))) {
                                    return json_encode(array(
                                        'status'=> 0,
                                        'msg'=> '请正确填写您收到的6位数字验证码！'
                                    ));
                                } else if ($request->$mode !== Cache::get('email')) {
                                    return json_encode(array(
                                        'status'=> 0,
                                        'msg'=> '注册邮箱与接收验证码的邮箱不一致！'
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
                                    $msg->uId = md5(uniqid());
                                    $msg->title='欢迎注册本站会员!';
                                    $msg->content='恭喜您成功通过邮箱'.$email.'注册本站会员!';
                                    $msg->read = 0;
                                    $msg->to = $un;
                                    $msg->save();
                                    return json_encode(array(
                                        'status'=> 1,
                                        'msg'=> '注册成功，已自动为您登录！',
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
                                } else if (intval($request->code) !== intval(Cache::get('sms'))) {
                                    return json_encode(array(
                                        'status'=> 0,
                                        'msg'=> '请正确填写您收到的6位数字验证码！'
                                    ));
                                } else if ($request->$mode !== Cache::get('phone')) {
                                    return json_encode(array(
                                        'status'=> 0,
                                        'msg'=> '注册手机与接收验证码的手机不一致！'
                                    ));
                                } else {
                                    $variable->save();
                                    $msg->uId = md5(uniqid());
                                    $msg->title='欢迎注册本站会员!';
                                    $msg->content='恭喜您成功通过手机'.$request->phone.'注册本站会员!';
                                    $msg->read = 0;
                                    $msg->to = $un;
                                    $msg->save();
                                    return json_encode(array(
                                        'status'=> 1,
                                        'msg'=> '注册成功！',
                                        'data'=> $variable
                                    ));
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
            'password',
            'captcha'
        );
        foreach($req as $key => $value) {
            array_push($recieve, $key);
        }
        if (count($req) > 3) {
            return json_encode(array(
                'status'=> 0,
                'msg'=> '参数错误！'
            ));
        } else if (count(array_diff($params, $recieve)) === 2) {
            $variable = null;
            if ($request->captcha !== Cache::get('captcha')) {
                return json_encode(array(
                    'status'=> 0,
                    'msg'=> '验证码错误！'
                ));
            } else if ($request->username) {
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
            } else {
                return json_encode(array(
                    'status'=> 0,
                    'msg'=> '登录失败，密码错误！'
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
    // 忘记密码
    public function password (Request $request) {
        $variable = User::where('uId', $request->uId)->first();
        $msg = new Message();
        $code = $variable->remember_token;
        $mode = null;
        if ($request->phone && !$request->email) {
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
                    if (!$variable->phone) {
                        return json_encode(array(
                            'status'=> 0,
                            'msg'=> '尚未绑定任何手机号，无法通过手机号修改密码！'
                        ));
                    }else if ($variable->phone === $request->phone) {
                        $variable -> password = md5($request->password);
                        $variable->update();
                        $msg->uId = md5(uniqid());
                        $msg->title='找回密码!';
                        $msg->content='恭喜您成功通过手机'.$request->phone.'找回密码,新密码是:('.$request->password.'),请牢记!';
                        $msg->read = 0;
                        $msg->to = $request->uId;
                        $msg->save();
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
        } else if (!$request->phone && $request->email) {
            if ($request->captcha !== $request->session()->get('captcha')) {
                return json_encode(array(
                    'status'=> 0,
                    'msg'=> '验证码输入有误！'
                ));
            } else if ($variable->email !== $request->email) {
                return json_encode(array(
                    'status'=> 0,
                    'msg'=> '您提供的邮箱与注册邮箱不一致！'
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
                    $msg->uId = md5(uniqid());
                    $msg->title='找回密码!';
                    $msg->content='恭喜您成功通过邮箱'.$request->email.'找回密码,新密码是:('.$request->password.'),请牢记!';
                    $msg->read = 0;
                    $msg->to = $request->uId;
                    $msg->save();
                    return json_encode(array(
                        'status'=> 1,
                        'msg'=> '密码修改成功，请牢记您的新密码！'
                    ));
                }
            }
        }
    }  
    // 发送邮件验证码
    public function sendCode (Request $request) {
        $uId = $request->uId;
        $type = intval($request->type);
        $captcha = $request->captcha;
        $variable = User::where('uId', $uId)->first();
        $email = $request->email;
        if ($type === 0) {
            $email = $request->email;
            if (!preg_match('/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/', $email)) {
                return json_encode(array(
                    'status'=>0,
                    'msg'=>'邮箱格式错误!'
                ));
            } else if ($captcha !== Cache::get('captcha')) {
                return json_encode(array(
                    'status'=>0,
                    'msg'=>'验证码错误!'
                ));
            } else if (User::where('email', $email)->first()) {
                return json_encode(array(
                    'status'=>0,
                    'msg'=>'该邮箱已被注册，请更换或者直接登录!'
                ));
            } else {
                $length = 6;
                $random = rand(pow(10,($length-1)), pow(10,$length)-1);
                $to = $email;
                $flag = Mail::send('remembermail', [
                    'name'=>$request->username,
                    'qrcode'=> $random
                ],function ($message) use($to) {
                    $message->to($to)->subject('收好你的密钥（视觉码农）');
                });
                Cache::put('code', $random, 10);
                Cache::put('email', $email, 10);
                return json_encode(array(
                    'status'=> 1,
                    'msg'=> '验证码已经发送至您的邮箱，请注意查收！'
                ));
            }
        } else if ($type === 1) {
            // 修改邮箱
            $email = $request->email;
            if (!preg_match('/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/', $email)) {
                return json_encode(array(
                    'status'=>0,
                    'msg'=>'邮箱格式错误!'
                ));
            } else if (User::where('email', $email)->first()) {
                return json_encode(array(
                    'status'=>0,
                    'msg'=>'该邮箱已被绑定,请更换新邮箱!'
                ));
            } else if ($captcha !== Cache::get('captcha')) {
                return json_encode(array(
                    'status'=>0,
                    'msg'=>'验证码错误!'
                ));
            } else {
                $length = 6;
                $random = rand(pow(10,($length-1)), pow(10,$length)-1);
                $to = $email;
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
        } else if ($typep === 2) {
            // 修改密码
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
    }
    public function updatePassword (Request $request) {
        $uId = $request->uId;
        $msg = new Message();
        $variable = User::where('uId', $uId)->first();
        if (!$request->old || !$request->new || !$request->captcha) {
            return json_encode(array(
                'status'=> 0,
                'msg'=> '参数有误！'
            ));
        } else if ($request->captcha !== Cache::get('captcha')) {
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
                $msg->uId = md5(uniqid());
                $msg->title='修改密码!';
                $msg->content='恭喜您成功修改密码,新密码是:('.$request->new.'),请妥善保存!';
                $msg->read = 0;
                $msg->to = $request->uId;
                $msg->save();
                return json_encode(array(
                    'status'=> 1,
                    'msg'=> '密码修改成功，请牢记您的新密码！'
                ));
            }
        }
    }
    // 删除用户（可以批量删除）
    public function remove (Request $request) {
        $uId = $request->uId;
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
                    'msg'=>'删除成功！',
                    'delete'=>$deleted,
                    'null'=>$notexist
                ));
            }
        } else {
            if ($notexist) {
                return json_encode(array(
                    'status'=>2,
                    'msg'=>'操作成功，您所删除的用户有不存在的！',
                    'data'=>$notexist
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
    public function updateUserInfo (Request $request) {
        $uId = $request->uId;
        $msg = new Message();
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
            $msg->uId = md5(uniqid());
            $msg->title='修改个人资料!';
            $msg->content='恭喜您成功修改个人资料!';
            $msg->read = 0;
            $msg->to = $request->uId;
            $msg->save();
            return json_encode(array(
                'status'=>1,
                'msg'=>'修改成功！',
                'data'=>$variable
            ));
        }else {
            return json_encode(array(
                'status'=>0,
                'msg'=>'修改失败，接收参数有误！',
            ));
        }
    }
    public function bindingPhone (Request $request) {
        $variable = User::where('uId', $request->uId)->first();
        $msg = new Message();
        $phone = $request->phone;
        $code = $request->code;
        if ($variable->phone){
            return json_encode(array(
                'status'=>0,
                'msg'=>'您已经绑定手机!'
            ));
        }else if (!$phone) {
            return json_encode(array(
                'status'=>0,
                'msg'=>'请填写新手机号!'
            ));
        } else if (count(User::where('phone', $phone)->first())) {
            return json_encode(array(
                'status'=>0,
                'msg'=>'该手机号已被绑定!'
            ));
        } else if (intval($code) !== Cache::get('sms')) {
            return json_encode(array(
                'status'=>0,
                'msg'=>'验证码错误!'
            ));
        } else if (!preg_match("/^1[34578]{1}\d{9}$/", $phone)) {
            return json_encode(array(
                'status'=>0,
                'msg'=>'手机号格式错误!'
            ));
        } else {
            $variable->phone = $phone;
            $variable->update();
            $msg->uId = md5(uniqid());
            $msg->title='绑定手机号码!';
            $msg->content='恭喜您成功更换绑定手机,新手机号码是:'.$request->new;
            $msg->read = 0;
            $msg->to = $request->uId;
            $msg->save();
            return json_encode(array(
                'status'=>1,
                'msg'=>'成功绑定手机号!'
            ));
        }
    }
    public function updatePhone (Request $request) {
        $variable = User::where('uId', $request->uId)->first();
        $msg = new Message();
        $old = $request->old;
        $new = $request->new;
        $code = $request->code;
        if (!$variable->phone){
            return json_encode(array(
                'status'=>0,
                'msg'=>'您尚未绑定手机号,请绑定手机号!'
            ));
        } else if ($variable->phone !== $old) {
            return json_encode(array(
                'status'=>0,
                'msg'=>'旧手机号填写有误,请重试!'
            ));
        } else if (!$new) {
            return json_encode(array(
                'status'=>0,
                'msg'=>'请填写新手机号!'
            ));
        } else if (intval($code) !== Cache::get('sms')) {
            return json_encode(array(
                'status'=>0,
                'msg'=>'验证码错误!'
            ));
        } else if (!preg_match("/^1[34578]{1}\d{9}$/", $new)) {
            return json_encode(array(
                'status'=>0,
                'msg'=>'手机号格式错误!'
            ));
        } else {
            $variable->phone = $new;
            $variable->update();
            $msg->uId = md5(uniqid());
            $msg->title='更换手机号码!';
            $msg->content='恭喜您成功更换绑定手机,新手机号码是:'.$request->new;
            $msg->read = 0;
            $msg->to = $request->uId;
            $msg->save();
            return json_encode(array(
                'status'=>1,
                'msg'=>'修改成功!'
            ));
        }
    }
    public function bindingEmail (Request $request) {
        $variable = User::where('uId', $request->uId)->first();
        $msg = new Message();
        $email = $request->email;
        $code = $request->code;
        $captcha = $request->captcha;
        if ($variable->email){
            return json_encode(array(
                'status'=>0,
                'msg'=>'您已经绑定邮箱!'.$variable->email
            ));
        } else if (!$email) {
            return json_encode(array(
                'status'=>0,
                'msg'=>'请填写要绑定的邮箱!'
            ));
        } else if (intval($code) !== intval($variable->remember_token)) {
            return json_encode(array(
                'status'=>0,
                'msg'=>'验证码错误!'
            ));
        } else if ($captcha !== Cache::get('captcha')) {
            return json_encode(array(
                'status'=>0,
                'msg'=>'图片验证码错误!'
            ));
        } else if (!preg_match('/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/', $email)) {
            return json_encode(array(
                'status'=>0,
                'msg'=>'邮箱格式错误!'
            ));
        } else {
            $variable->email = $email;
            $variable->remember_token = '';
            $variable->update();
            $msg->uId = md5(uniqid());
            $msg->title='绑定邮箱!';
            $msg->content='恭喜您成功更换绑定邮箱,新邮箱是:'.$request->email;
            $msg->read = 0;
            $msg->to = $request->uId;
            $msg->save();
            return json_encode(array(
                'status'=>1,
                'msg'=>'绑定成功!'
            ));
        }
    }
    public function updateEmail (Request $request) {
        $variable = User::where('uId', $request->uId)->first();
        $msg = new Message();
        $old = $request->old;
        $new = $request->new;
        $code = $request->code;
        $captcha = $request->captcha;
        if (!$variable->email){
            return json_encode(array(
                'status'=>0,
                'msg'=>'您尚未绑定邮箱,请前往绑定!'
            ));
        } else if ($variable->email !== $old) {
            return json_encode(array(
                'status'=>0,
                'msg'=>'旧邮箱填写错误,请重试!'
            ));
        } else if (!$new) {
            return json_encode(array(
                'status'=>0,
                'msg'=>'请填写新邮箱!'
            ));
        } else if (intval($code) !== intval($variable->remember_token)) {
            return json_encode(array(
                'status'=>0,
                'msg'=>'验证码错误!'
            ));
        } else if ($captcha !== Cache::get('captcha')) {
            return json_encode(array(
                'status'=>0,
                'msg'=>'图片验证码错误!'
            ));
        } else if (!preg_match('/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/', $new)) {
            return json_encode(array(
                'status'=>0,
                'msg'=>'邮箱格式错误!'
            ));
        } else {
            $variable->email = $new;
            $variable->remember_token = '';
            $variable->update();
            $msg->uId = md5(uniqid());
            $msg->title='更换邮箱!';
            $msg->content='恭喜您成功更换绑定邮箱,新邮箱是:'.$request->new;
            $msg->read = 0;
            $msg->to = $request->uId;
            $msg->save();
            return json_encode(array(
                'status'=>1,
                'msg'=>'修改成功!'
            ));
        }
    }
    public function updateFace (Request $request) {
        $uId = $request->uId;
        $msg = new Message();
        $variable = User::where('uId', $uId)->first();
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
                    //  if ($type !== 'image/jpeg' || $type !== 'image/png') {
                    //      return json_encode(array(
                    //          'status'=>0,
                    //          'msg'=> '只能上传图片(jpg,png)!'
                    //      ));
                    //  }
                     //临时绝对路径  
                     $realPath = $files->getRealPath();  
                     $filename = date('YmdHiS').uniqid().'.'.$ext;  
                     $bool = Storage::disk('uploads')->put($filename, file_get_contents($realPath));
                     if ($bool) {
                         $data = array(
                             'path'=>'http://'.$request->server('SERVER_ADDR').'/api/'.'storage/app/uploads',
                             'file'=> $filename,
                             'ext'=> $ext,
                             'size'=>$size,
                             'type'=>$type
                         );
                         $variable->cover = $filename;
                         $variable->update();
                         $msg->uId = md5(uniqid());
                        $msg->title='更换头像!';
                        $msg->content='恭喜您成功更换头像！';
                        $msg->read = 0;
                        $msg->to = $request->uId;
                        $msg->save();
                         return json_encode(array(
                             'status'=>1,
                             'msg'=>'头像修改成功！',
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
        $uId = $request->uId;
        $data = $request->data;
        $user = User::where('uId', $uId)->first();
        if (!$uId) {
            return json_encode(array(
                'status'=>0,
                'msg'=>'用户id必填！'
            ));
        } else if (!$data) {
            return json_encode(array(
                'status'=>0,
                'msg'=>'base64不能为空！'
            ));
        } else if (!count($user)) {
            return json_encode(array(
                'status'=>0,
                'msg'=>'不存在该用户！'
            ));
        } else {
            $files = explode(',', $data);
            $output_file = date('YmdHiS').uniqid().'.jpg';
            $boolean = Storage::disk('uploads')->put($output_file, base64_decode($files[1]));
            if ($boolean) {
                $user->cover = $output_file;
                $user->update();
                $data = array(
                    'path'=>'http://'.$request->server('SERVER_ADDR').'/api/'.'storage/app/uploads',
                    'file'=>$output_file
                );
                return json_encode(array(
                    'status'=>1,
                    'msg'=>'头像修改成功！',
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
    // 积分
    public function score (Request $request) {
        $uId = $request -> uId;
        $variable = User::where('uId', $uId)->first();
        if ($variable) {
            return json_encode(array(
                'status'=>1,
                'msg'=>'获取成功!',
                'data'=>array(
                    'balance'=>$variable->b_score,
                    'total'=>$variable->b_score
                )
            ));
        } else {
            return json_encode(array(
                'status'=>0,
                'msg'=>'不存在该用户!'
            ));
        }
    }
    public function userInfo (Request $request) {
        $uId = $request->uId;
        $users = User::where('uId', $uId)->first();
        if (count($users)) {
            return json_encode(array(
                'status'=>1,
                'msg'=>'获取成功！',
                'data'=>$users
            ));
        } else {
            return json_encode(array(
                'status'=>0,
                'msg'=>'不存在的用户！'
            ));
        }
    }
    public function list (Request $request) {
        $limit = $request->limit;
        $limit = json_decode($limit);
        $param = array();
        $between = $request->between;
        $between = json_decode($between);
        $user = User::where(function($query) use($limit, $between){
            if (count($limit)) {
                foreach($limit as $key => $val) {
                    $query->where($key, $val);
                }
            }
            if (count($between)) {
                $query->whereBetween('created_at', [$between->start, $between->end]);
            }
        })->orderBy('updated_at', $request->sort)->paginate($request->pagesize);
        return json_encode(array(
            'status'=>1,
            'msg'=>'获取成功！',
            'data'=>$user
        ));
    }
}
