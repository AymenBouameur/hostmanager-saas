<?php
use MajorMedia\UserPlus\Http\Users;

Route::group([
    'prefix' => 'getApi/v1/endpoint',
], function () {

    Route::post('login', Users::class . '@login');
    Route::post('register', Users::class . '@register');
    Route::get('getProfile', Users::class . '@getProfile');
    Route::post('findAccount', Users::class . '@findAccount');
    Route::post('verifyOTP', Users::class . '@verifyOTP');
    Route::post('resetPassword', Users::class . '@resetPassword');
    Route::post('updateProfile', Users::class . '@updateProfile');
    Route::post('changePassword', Users::class . '@changePassword');
    Route::post('logout', Users::class . '@logout');
    Route::delete('deleteAccount', Users::class . '@deleteAccount');
    Route::post('authenticateByToken', Users::class . '@authenticateByToken');
    Route::get('default_avatar', Users::class . '@getDefaultAvatar');
    Route::get('refreshToken', Users::class . '@refreshToken');

});