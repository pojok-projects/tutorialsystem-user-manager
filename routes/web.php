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

    // Show Routes
    $router->get('user/{id}', 'UserController@show');
    $router->get('user/following/{id}', 'UserFollowingController@show');

    // Store Routes
    $router->post('/user/store', 'UserController@create');
    $router->post('/user/following/store', 'UserFollowingController@create');
	
	// Search Routes
	$router->post('/user/search', 'UserController@search');
	$router->post('/user/following/search', 'UserFollowingController@search');

	// Update Routes
	$router->put('/user/update/{id}', 'UserController@update');
	$router->put('/user/following/update/{id}', 'UserFollowingController@update');

	// Delete Routes
	$router->delete('/user/delete/{id}', 'UserController@destroy');
	$router->delete('/user/following/delete/{id}', 'UserFollowingController@destroy');

});