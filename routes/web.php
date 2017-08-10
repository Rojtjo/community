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
    return view('home');
});

// User Profile Routes
Route::group(['prefix' => 'profiel'], function() {
    Route::get('bewerken', ['as' => 'user.edit', 'uses' => 'MemberController@edit']);
});

// Authentication Routes
Route::group(['prefix' => 'gebruiker'], function() {
    // Login and Logout Routes...
    Route::get('inloggen', 'Auth\LoginController@showLoginForm')->name('login');
    Route::post('inloggen', 'Auth\LoginController@login');
    Route::get('uitloggen', 'Auth\LoginController@logout')->name('logout');

    // Registration Routes...
    Route::get('aanmelden', 'Auth\RegisterController@showRegistrationForm')->name('register');
    Route::post('aanmelden', 'Auth\RegisterController@register');

    // Password Reset Routes...
    Route::get('wachtwoord/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
    Route::post('wachtwoord/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
    Route::get('wachtwoord/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
    Route::post('wachtwoord/reset', 'Auth\ResetPasswordController@reset');
});