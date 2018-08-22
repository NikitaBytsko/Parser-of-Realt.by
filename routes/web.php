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

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::resource('office', 'OfficeController')->middleware('admin');
Route::post('/office', 'OfficeController@address_index')->middleware('admin');

Route::get('/parse/codes', 'ParseCodesController@create')->middleware('admin');
Route::get('/parse/offices', 'ParseTablesController@create')->middleware('admin');

