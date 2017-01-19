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

Route::get('/', 'Front\HomeController@index')->name('home_index');
Route::get('search', 'Front\SearchController@index')->name('search_index');
Route::get('front/cron', 'Front\CronController@index')->name('front_cron_index');
