<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['namespace' => 'Api'], function () {
    $exceptCreateAndEdit = ['except' => ['create', 'edit']];
    Route::resource('categories', 'CategoryController', $exceptCreateAndEdit);
    Route::resource('genres', 'GenreController', $exceptCreateAndEdit);
    Route::resource('cast_members', 'CastMemberController', $exceptCreateAndEdit);
    Route::resource('videos', 'VideoController', $exceptCreateAndEdit);
});
