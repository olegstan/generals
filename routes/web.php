<?php


Route::group(['prefix' => '/api/v1', 'middleware' => ['api.token', 'cors']], function () {
    Route::any('call/{target}/{method}', ['middleware' => ['active'], 'as' => 'api.v1.call', 'uses' => 'App\Api\V1\Controllers\IndexController@index']);

//    Route::group(['prefix' => 'auth'], function () {
//        Route::post('login', ['as' => 'api.v1.auth.login', 'uses' => 'App\Api\V1\Controllers\Common\AuthController@auth']);
//        Route::post('forget', ['as' => 'api.v1.auth.forget', 'uses' => 'App\Api\V1\Controllers\Common\AuthController@forget']);
//        Route::post('register', ['as' => 'api.v1.auth.register', 'uses' => 'App\Api\V1\Controllers\Common\AuthController@postRegister']);
//        Route::get('logout', ['as' => 'api.v1.auth.logout', 'middleware' => 'api.auth', 'uses' => 'App\Api\V1\Controllers\Common\AuthController@logout']);
//    });
});


Route::get('/', function () {
    return view('welcome');
});
