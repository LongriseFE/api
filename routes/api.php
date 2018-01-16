<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//上传文件
Route::match(['get', 'post'], '/upfile', 'FileController@uploadFile')->name('uploadFile');
Route::match(['get', 'post'], '/base64', 'FileController@base64')->name('base64');
Route::match(['get', 'post'], '/delete', 'FileController@delete')->name('delete');
Route::match(['get', 'post'], '/download', 'FileController@downfile')->name('downfile');
Route::match(['get','post'],'/update/face', 'UserController@updateFace')->name('updateFace');
//验证码
Route::group(['middleware'=>['web']], function () {
    Route::match(['get', 'post'],'/captcha', 'ToolController@captcha')->name('captcha');
    Route::match(['get', 'post'],'/getcaptcha', 'ToolController@getcaptcha')->name('getcaptcha');
    Route::match(['get', 'post'],'/sms', 'ToolController@sms')->name('sms');
    Route::match(['get', 'post'],'/mail', 'ToolController@mail')->name('mail');
    Route::match(['get', 'post'],'/sendcode', 'UserController@sendCode')->name('sendCode');
    //用户注册
    Route::match(['get', 'post'],'/register', 'UserController@register')->name('register');
    Route::match(['get', 'post'],'/login', 'UserController@login')->name('login');
    Route::match(['get','post'],'/password', 'UserController@password')->name('password');
    Route::match(['get','post'],'/remove/user', 'UserController@remove')->name('removeuser');
    Route::match(['get', 'post'],'/update/userinfo', 'UserController@updateUserInfo')->name('updateUserInfo');
    Route::match(['get', 'post'],'/update/password', 'UserController@updatePassword')->name('updatePassword');
    Route::match(['get','post'],'/update/phone', 'UserController@updatePhone')->name('updatePhone');
    Route::match(['get','post'],'/update/email', 'UserController@updateEmail')->name('updateEmail');
});

// 消息模块
Route::match(['get','post'],'/message', 'MessageController@message')->name('message');
Route::match(['get','post'],'/update/message', 'MessageController@read')->name('readmessage');
Route::match(['get','post'],'/remove/message', 'MessageController@remove')->name('removemessage');
Route::match(['get','post'],'/send/message', 'MessageController@add')->name('addmessage');
// 积分
Route::match(['get','post'],'/score', 'UserController@score')->name('score');
Route::match(['get','post'],'/set/score', 'ScoreController@add')->name('setscore');
// 文件柜
Route::match(['get','post'],'/dir/make', 'DriverController@makeDir')->name('makeDir');
Route::match(['get','post'],'/dir/get', 'DriverController@getDir')->name('getDir');
Route::match(['get','post'],'/dir/del', 'DriverController@delDir')->name('delDir');
Route::match(['get','post'],'/dir/update', 'DriverController@updateDir')->name('updateDir');
Route::match(['get','post'],'/dir/upload', 'DriverController@upload')->name('uploadDir');
Route::match(['get','post'],'/dir/type', 'DriverController@category')->name('Dircategory');
// 项目
Route::match(['get','post'],'/project/list', 'ProjectController@list')->name('ProjectList');
Route::match(['get','post'],'/project/add', 'ProjectController@add')->name('ProjectAdd');
Route::match(['get','post'],'/project/edit', 'ProjectController@edit')->name('ProjectEdit');
Route::match(['get','post'],'/project/del', 'ProjectController@del')->name('ProjectDel');
Route::match(['get','post'],'/project/group', 'ProjectController@groups')->name('Projectgroup');