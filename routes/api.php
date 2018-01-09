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
Route::get('/getcaptcha', 'UserController@captcha')->name('captcha');
Route::get('/checkcaptcha', 'UserController@checkcaptcha')->name('checkcaptcha');
Route::get('/sendmail/{uId}', 'UserController@sendmail')->name('sendmail');
Route::get('/editpassword/{uId}', 'UserController@editpassword')->name('editpassword');
Route::get('/qrcodeinfo/{uId}', 'UserController@qrcodeinfo')->name('qrcodeinfo');
Route::get('/destroy/user/{uId}', 'UserController@destroy')->name('destroyuser');
Route::get('/update/user/{uId}', 'UserController@updateUserInfo')->name('updateuserInfo');
Route::get('/update/phone/{uId}', 'UserController@updatePhone')->name('updatePhone');

Route::match(['get', 'post'], '/upfile', 'FileController@uploadFile')->name('uploadFile');
Route::match(['get', 'post'], '/base64', 'FileController@base64')->name('base64');