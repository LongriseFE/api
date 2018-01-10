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