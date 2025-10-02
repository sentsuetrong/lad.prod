<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->group('', ['filter' => 'cors'], static function (RouteCollection $routes) {
  $routes->group('api/v1', ['namespace' => 'App\Controllers\Api\V1'],  static function (RouteCollection $routes) {
    $routes->post('auth/login', 'AuthController::jwtLogin');
    $routes->post('auth/register', 'AuthController::register');

    $routes->post('files/upload', 'FileController::upload');
    $routes->get('files/(:segment)/download', 'FileController::download/$1');

    $routes->options('(:any)', static function () {});
  });
});

// $routes->get('/', 'Home::index');
// service('auth')->routes($routes);
