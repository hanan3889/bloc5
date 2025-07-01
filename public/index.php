<?php

/**
 * Front controller
 *
 * PHP version 7.0
 */

session_start();

/**
 * Composer
 */
require dirname(__DIR__) . '/vendor/autoload.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * Error and Exception handling
 */
error_reporting(E_ALL);
set_error_handler('Core\Error::errorHandler');
set_exception_handler('Core\Error::exceptionHandler');


/**
 * Routing
 */
$router = new Core\Router();

// Add the routes
$router->add('', ['controller' => 'Home', 'action' => 'index']);
$router->add('login', ['controller' => 'User', 'action' => 'login']);
$router->add('register', ['controller' => 'User', 'action' => 'register']);
$router->add('logout', ['controller' => 'User', 'action' => 'logout', 'private' => true]);
$router->add('account', ['controller' => 'User', 'action' => 'account', 'private' => true]);
$router->add('product', ['controller' => 'Product', 'action' => 'index', 'private' => true]);
$router->add('product/success', ['controller' => 'Product', 'action' => 'success']);
$router->add('product/contact/{id:\d+}', ['controller' => 'Product','action' => 'contact','private' => true]);
$router->add('product/success', ['controller' => 'Product', 'action' => 'success']);
$router->add('product/{id:\d+}', ['controller' => 'Product', 'action' => 'show']);

// API routes
$router->add('api/products/all', ['controller' => 'Api', 'action' => 'products']);
$router->add('api/cities', ['controller' => 'Api', 'action' => 'cities']);
$router->add('api/users/register', ['controller' => 'User', 'action' => 'register']);
$router->add('api/users/login', ['controller' => 'User', 'action' => 'login']);
$router->add('api/users/{id:\d+}', ['controller' => 'User', 'action' => 'findById']);

$router->add('{controller}/{action}');



/*
 * Gestion des erreurs dans le routing
 */
try {
    $router->dispatch($_SERVER['QUERY_STRING']);
} catch(Exception $e){
    switch($e->getMessage()){
        case 'You must be logged in':
            header('Location: /login');
            break;
    }
}
