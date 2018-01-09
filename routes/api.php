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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/register/{mode}', 'UserController@register')->name('register');
Route::get('/login', 'UserController@login')->name('login');
Route::get('/online', 'UserController@online')->name('online');
Route::get('/remember/{mode}/{uId}', 'UserController@remember')->name('remember');
Route::get('/sms', 'UserController@sms')->name('sms');
Route::get('/sendmail/{uId}', 'UserController@sendmail')->name('sendmail');
Route::get('/editpassword/{uId}', 'UserController@editpassword')->name('editpassword');
Route::get('/qrcodeinfo/{uId}', 'UserController@qrcodeinfo')->name('qrcodeinfo');
Route::get('/destroy/user/{uId}', 'UserController@destroy')->name('destroyuser');
Route::get('/update/user/{uId}', 'UserController@updateUserInfo')->name('updateuserInfo');
Route::get('/update/phone/{uId}', 'UserController@updatePhone')->name('updatePhone');
//上传文件
Route::match(['get', 'post'], '/upfile', 'FileController@uploadFile')->name('uploadFile');
Route::match(['get', 'post'], '/base64', 'FileController@base64')->name('base64');
//验证码
Route::group(['middleware'=>['web']], function () {
    Route::match(['get', 'post'],'/captcha', 'ToolController@captcha')->name('captcha');
    Route::match(['get', 'post'],'/getcaptcha', 'ToolController@getcaptcha')->name('getcaptcha');
    Route::match(['get', 'post'],'/sms', 'ToolController@sms')->name('sms');
    Route::match(['get', 'post'],'/mail', 'ToolController@mail')->name('mail');
});