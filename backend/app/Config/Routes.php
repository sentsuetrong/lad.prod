<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->group('', ['filter' => 'cors'], static function (RouteCollection $routes) {
  $routes->group('api/v1', ['namespace' => 'App\Controllers\Api\V1'],  static function (RouteCollection $routes) {
    $routes->post('auth/login', 'AuthController::jwtLogin');
    $routes->post('auth/register', 'AuthController::register');

    $routes->get('files', 'FileController::files');
    $routes->post('files/upload', 'FileController::upload');
    $routes->get('files/(:segment)', 'FileController::download/$1/inline', ['cache' => 900]);
    $routes->get('files/(:segment)/download', 'FileController::download/$1', ['filter' => 'throttle']);
    $routes->get('files/(:segment)/thumbnail', 'FileController::thumbnail/$1', ['cache' => 900]);
    // $routes->delete('files/(:segment)', 'FileController::delete');

    $routes->options('(:any)', static function () {});
  });
});

// $routes->get('/', 'Home::index');
// service('auth')->routes($routes);
