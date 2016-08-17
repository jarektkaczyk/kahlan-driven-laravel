<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('admin', function () {
    return 'admin area for logged in user only';
})->middleware('auth');

Route::get('login', function () {
    return 'login form for guests only';
})->middleware('guest');

Route::get('session-test', function () {
    return session('session_test');
});
