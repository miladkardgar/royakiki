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

Route::get('/', 'facebookController@index')->name('home');
Route::get('/callback', 'facebookController@callback')->name('callback');
Route::get('/getToken', 'facebookController@getToken')->name('getToken');
Route::get('/pageInfo', 'facebookController@pageInfo')->name('pageInfo');
Route::get('/comment/{id}', 'facebookController@getComment')->name('getComment');
Route::get('/postList', 'facebookController@postList')->name('postList');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
