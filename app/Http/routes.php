<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

get('admin', function () {
    return 'admin area for logged in user only';
})->middleware('auth');

get('login', function () {
    return 'login form for guests only';
})->middleware('guest');
