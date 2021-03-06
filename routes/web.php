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
Route::match(['get', 'post'], '/login', 'Auth\LoginController@login')->name('auth_login');
Route::get('logout', 'Auth\LoginController@logout')->name('logout');
Route::get('front/cron', 'Front\CronController@index')->name('front_cron_index');
Route::get('front/cron_manual', 'Front\CronController@cronManual')->name('front_cron_manual');
Route::get('front/book_auto', 'Front\CronController@bookAuto')->name('front_cron_book_auto');
Route::get('front/test_email', 'Front\CronController@testEmail')->name('front_cron_test_email');
Route::get('front/test_bookauto', 'Front\CronController@testBookAuto')->name('front_cron_test_book');
Route::get('front/push_phone', 'Front\PhoneController@index')->name('front_cron_push_phone');
Route::get('front/push_phone_define', 'Front\PhoneController@pushPhoneDefine')->name('front_cron_push_phone_define');
Route::get('front/test', 'Front\HomeController@testPage')->name('front_cron_test');

Route::group(['prefix' => 'sale', 'middleware' => 'auth.sale'], function () {
    Route::get('/', 'Front\SearchController@index')->name('home_index');
    Route::get('/search', 'Front\SearchController@index')->name('search_index');
    Route::get('setting', 'Front\SettingController@index')->name('setting_index');
    Route::get('setting/edit/{setting_id}', 'Front\SettingController@edit')->name('setting_edit');
    Route::post('setting/update', 'Front\SettingController@update')->name('setting_update');
    Route::get('setting/delete/{setting_id}', 'Front\SettingController@delete')->name('setting_delete');
});

Route::group(['prefix' => 'admin', 'middleware' => 'auth.admin'], function () {

    Route::get('dashboard', 'Admin\HomeController@index')->name('admin_dashboard');
    Route::match(['get', 'post'], 'profile/edit', 'Admin\UserProfileController@edit')->name('admin_profile_edit');
    Route::match(['get', 'post'], 'phone/black-list','Admin\PhoneController@index')->name('admin_phone_black_list');

    // Manage user
    Route::get('user-manage', 'Admin\UserManageController@index')->name('admin_user_manage');
    Route::match(['get', 'post'], 'user-manage/edit','Admin\UserManageController@edit')->name('admin_user_edit');
    Route::match(['get', 'post'], 'user-manage/add', 'Admin\UserManageController@add')->name('admin_user_create');
    Route::post('user-manage/delete', 'Admin\UserManageController@delete')->name('admin_user_delete');

});
