<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//login
Route::group(['prefix' => 'auth'], function () {
    Route::post('login', 'API\AuthController@login');
    Route::post('signup', 'API\AuthController@signup');

    Route::group(['middleware' => 'auth:api'], function() {
        Route::get('logout', 'API\AuthController@logout');
        Route::get('user', 'API\AuthController@user');
    });
});

Route::group(['middleware' => 'auth:api'], function() {
    Route::post('get-all-videos', 'API\VideoController@getAllVideos');
    Route::post('add-video', 'API\VideoController@addVideo');
    Route::patch('update-video/{id}', 'API\VideoController@updateVideo');
    Route::delete('delete-video/{id}', 'API\VideoController@deleteVideo');
});



Route::post('/get-all-video-notes', 'API\VideoNoteController@getAllVideoNotes');
Route::post('/create-video-note', 'API\VideoNoteController@createVideoNote');
Route::patch('/update-video-note/{id}', 'API\VideoNoteController@updateVideoNote');
Route::delete('/delete-video-note/{id}', 'API\VideoNoteController@deleteVideoNote');

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
