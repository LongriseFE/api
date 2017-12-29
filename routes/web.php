<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/register/{mode}', 'UserController@register')->name('register');
Route::get('/login', 'UserController@login')->name('login');
Route::get('/online', 'UserController@online')->name('online');
Route::get('/remember/{mode}/{uId}/{code}', 'UserController@remember')->name('remember');
Route::get('/sms/{phone}', 'UserController@sms')->name('sms');