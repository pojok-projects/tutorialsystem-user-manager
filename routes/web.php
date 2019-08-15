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
$router->group(['prefix'=>'v1/user'], function() use($router){
    $router->get('/', 'UserController@index');
    $router->post('/store', 'UserController@create');
	$router->get('/{id}', 'UserController@show');
	$router->post('/search', 'UserController@search');
	$router->put('/update/{id}', 'UserController@update');
	$router->delete('/delete/{id}', 'UserController@destroy');
});

// USER FOLLOWING ROUTE
// $router->group(['prefix'=>'v1/user/following'], function() use($router){
//     $router->get('/', 'UserFollowingController@index');
 //    $router->post('/store', 'UserController@create');
	// $router->get('/{id}', 'UserController@show');
	// $router->post('/search', 'UserController@search');
	// $router->put('/update/{id}', 'UserController@update');
	// $router->delete('/delete/{id}', 'UserController@destroy');
// });