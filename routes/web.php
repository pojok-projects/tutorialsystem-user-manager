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
$router->group(['prefix'=>'v1'], function() use($router){
	// Index Routes
    $router->get('/user', 'UserController@index');
    $router->get('/user/following', 'UserFollowingController@index');
    $router->get('/user/follower', 'UserFollowerController@index');
    $router->get('/user/likevideo', 'UserLikeVideoController@index');

    // Show Routes
    $router->get('user/{id}', 'UserController@show');
    $router->get('user/following/{id}', 'UserFollowingController@show');
    $router->get('user/follower/{id}', 'UserFollowerController@show');
    $router->get('user/likevideo/{id}', 'UserLikeVideoController@show');

    // Store Routes
    $router->post('/user/store', 'UserController@create');
    $router->post('/user/following/store', 'UserFollowingController@create');
    $router->post('/user/follower/store', 'UserFollowerController@create');
    $router->post('/user/likevideo/store', 'UserLikeVideoController@create');
	
	// Search Routes
	$router->post('/user/search', 'UserController@search');
	$router->post('/user/following/search', 'UserFollowingController@search');
	$router->post('/user/follower/search', 'UserFollowerController@search');
	$router->post('/user/likevideo/search', 'UserLikeVideoController@search');

	// Update Routes
	$router->put('/user/update/{id}', 'UserController@update');
	$router->put('/user/following/update/{id}', 'UserFollowingController@update');
	$router->put('/user/follower/update/{id}', 'UserFollowerController@update');
	$router->put('/user/likevideo/update/{id}', 'UserLikeVideoController@update');

	// Delete Routes
	$router->delete('/user/delete/{id}', 'UserController@destroy');
	$router->delete('/user/following/delete/{id}', 'UserFollowingController@destroy');
	$router->delete('/user/follower/delete/{id}', 'UserFollowerController@destroy');
	$router->delete('/user/likevideo/delete/{id}', 'UserLikeVideoController@destroy');

});