<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

// USER ROUTE
$router->group(['prefix'=>'upa/v1'], function() use($router){
	// Index Routes
    $router->get('/user', 'UserController@index');

    // Show Routes
    $router->get('user/{id}', 'UserController@show');
    $router->get('user/following/{id}', 'UserFollowingController@show');
    $router->get('user/follower/{id}', 'UserFollowerController@show');
    // $router->get('user/likevideo/{id}', 'UserLikeVideoController@show');
    // $router->get('user/dislikevideo/{id}', 'UserDislikeVideoController@show');
    // $router->get('user/savedvideo/{id}', 'UserSavedVideoController@show');
    // $router->get('user/historyvideo/{id}', 'UserHistoryVideoController@show');

    // Store Routes
    $router->post('/user/store', 'UserController@create');
    $router->post('/user/following/store/{id}', 'UserFollowingController@create');
    $router->post('/user/follower/store/{id}', 'UserFollowerController@create');
    // $router->post('/user/likevideo/store', 'UserLikeVideoController@create');
    // $router->post('/user/dislikevideo/store', 'UserDislikeVideoController@create');
    // $router->post('/user/savedvideo/store', 'UserSavedVideoController@create');
    // $router->post('/user/historyvideo/store', 'UserHistoryVideoController@create');
	
	// Search Routes
	$router->get('/user/search', 'UserController@search');

	// Update Routes
	$router->put('/user/update/{id}', 'UserController@update');

	// Delete Routes
	$router->delete('/user/delete/{id}', 'UserController@destroy');
	$router->delete('/user/following/delete/{id_user}/{id_following}', 'UserFollowingController@destroy');
	$router->delete('/user/follower/delete/{id_user}/{id_follower}', 'UserFollowerController@destroy');
	// $router->delete('/user/likevideo/delete/{id}', 'UserLikeVideoController@destroy');
	// $router->delete('/user/dislikevideo/delete/{id}', 'UserDislikeVideoController@destroy');
	// $router->delete('/user/savedvideo/delete/{id}', 'UserSavedVideoController@destroy');
	// $router->delete('/user/historyvideo/delete/{id}', 'UserHistoryVideoController@destroy');

});