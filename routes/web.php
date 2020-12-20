<?php

/** @var Router $router */

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

use Laravel\Lumen\Routing\Router;

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => '/locale'], function() use ($router) {
    $router->get('/', 'LocaleController@list');
    $router->get('/{id}', 'LocaleController@get');
    $router->post('/', 'LocaleController@create');
    $router->put('/{id}', 'LocaleController@update');
    $router->delete('/{id}', 'LocaleController@delete');
});

$router->group(['prefix' => '/month'], function() use ($router) {
    $router->get('/', 'MonthController@list');
    $router->get('/{id}', 'MonthController@get');
    $router->post('/', 'MonthController@create');
    $router->put('/{id}', 'MonthController@update');
    $router->delete('/{id}', 'MonthController@delete');
});

$router->group(['prefix' => '/season'], function() use ($router) {
    $router->get('/', 'SeasonController@list');
    $router->get('/{id}', 'SeasonController@get');
    $router->post('/', 'SeasonController@create');
    $router->put('/{id}', 'SeasonController@update');
    $router->delete('/{id}', 'SeasonController@delete');
});

$router->group(['prefix' => '/category'], function() use ($router) {
    $router->get('/', 'CategoryController@list');
    $router->get('/{id}', 'CategoryController@get');
    $router->post('/', 'CategoryController@create');
    $router->put('/{id}', 'CategoryController@update');
    $router->delete('/{id}', 'CategoryController@delete');
});

$router->group(['prefix' => '/food'], function() use ($router) {
    $router->get('/', 'FoodController@list');
    $router->get('/{id}', 'FoodController@get');
    $router->post('/', 'FoodController@create');
    $router->put('/{id}', 'FoodController@update');
    $router->delete('/{id}', 'FoodController@delete');

    $router->post('/{id}/image', 'FoodController@updateImage');

    $router->get('/category/{id}', 'FoodController@listByCategory');
    $router->get('/month/{id}', 'FoodController@listByMonth');
});
