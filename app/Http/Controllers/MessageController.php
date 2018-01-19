<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Model\Message;
use App\Http\Model\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MessageController extends Controller
{
    public function message (Request $request) {
        $uId = $request->uId;
        $recieve = $request->recieve;
        $read = $request->read;
        $variable = null;
        if (!$uId) {
            return json_encode(array(
                'status'=>0,
                'msg'=>'请填写用户id!'
            ));
        } else if (!count(User::where('uId', $uId)->first())) {
            return json_encode(array(
                'status'=>0,
                'msg'=>'不存在改用户！'
            ));
        }else if (!$recieve || intval($recieve) === -1) {
            // 查询接收和发送的
            if (!$read || intval($read) === -1) {
                $variable = Message::where('from', $uId)->orwhere('to', $uId)->orderBy('updated_at', 'desc')->paginate($request->pagesize);
            } else {
                $variable = Message::where('from', $uId)->orwhere('to', $uId)->where('read', $read)->orderBy('updated_at', 'desc')->paginate($request->pagesize);
            }
        } else if (intval($recieve) === 0) {
            if (!$read || intval($read) === -1) {
                $variable = Message::where('to', $uId)->paginate($request->pagesize)->orderBy('updated_at', 'desc');
            } else {
                $variable = Message::where('to', $uId)->where('read', $read)->orderBy('updated_at', 'desc')->paginate($request->pagesize);
            }
        } else if (intval($recieve) === 1) {
            if (!$read || intval($read) === -1) {
                $variable = Message::where('from', $uId)->orderBy('updated_at', 'desc')->paginate($request->pagesize);
            } else {
                $variable = Message::where('from', $uId)->where('read', $read)->orderBy('updated_at', 'desc')->paginate($request->pagesize);
            }
        } else {
            return json_encode(array(
                'status'=>0,
                'msg'=>'参数错误!'
            ));
        }
        return json_encode(array(
            'status'=>1,
            'msg'=>'获取成功!',
            'data'=> $variable
        ));
    }
    public function read (Request $request) {
        $uId = $request->uId;
        $mId = $request->mId;
        $variable = Message::where('to', $uId)->where('uId', $mId)->first();
        if (!$variable) {
            return json_encode(array(
                'status'=>0,
                'msg'=>'不存在该条消息!'
            ));
        } else {
            if ($variable->read) {
                return json_encode(array(
                    'status'=>2,
                    'msg'=>'该条消息已读!'
                ));
            } else {
                $variable->read = 1;
                $variable->update();
                return json_encode(array(
                    'status'=>1,
                    'msg'=>'消息已读!',
                    'data'=> $variable
                ));
            }
        }
    }
    public function remove (Request $request) {
        $uId = $request->uId;
        $mId = $request->mId;
        $variable = Message::where('to', $uId)->where('uId', $mId)->first();
        if (!$variable) {
            return json_encode(array(
                'status'=>0,
                'msg'=>'不存在该条消息!'
            ));
        } else {
            $variable->delete();
            return json_encode(array(
                'status'=>1,
                'msg'=>'成功删除消息!',
                'data'=> $variable
            ));
        }
    }
    public function add (Request $request) {
        $title = $request->title;
        $content = $request->content;
        $from = $request->from;
        $to = $request->to;
        $variable = new Message();
        if (count($request->all()) !== 4) {
            return json_encode(array(
                'status'=>0,
                'msg'=>'参数错误!'
            ));
        } else {
            if (!User::where('uId',$from)->first()) {
                return json_encode(array(
                    'status'=>0,
                    'msg'=>'发送人不存在!'
                ));
            } else if (!User::where('uId',$to)->first()) {
                return json_encode(array(
                    'status'=>0,
                    'msg'=>'收件人不存在!'
                ));
            } else if (!$title || !$content || strlen($content) < 10){
                return json_encode(array(
                    'status'=>0,
                    'msg'=>'标题和消息正文不能为空,且正文字数必须大于10个!'
                ));
            } else {
                foreach($request->all() as $key => $val) {
                    $variable->$key = $val;
                }
                $variable->uId = md5(uniqid());
                $variable->read = 0;
                $variable->save();
                return json_encode(array(
                    'status'=>1,
                    'msg'=>'消息发送成功!'
                ));
            }
        }
    }
}
