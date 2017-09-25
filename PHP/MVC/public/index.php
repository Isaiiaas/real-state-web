<?php
ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 100);
ini_set('session.cookie_lifetime', 60 * 60 * 24 * 100);
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
session_start();

/**
 * Front controller
 *
 * PHP version 5.4
 */
 
require '../vendor/autoload.php';

/**
 * Twig
 */
Twig_Autoloader::register();

error_reporting(E_ALL);//Para o sistema poder mostrar todos os erros que ocorrer na plataforma.
set_error_handler('Core\Error::errorHandle');//Estamos setando para que, sempre que o sistema encontre um erro, ele execute a função errorHandle dentro da nossa classe Error existente dentro da pasta Core.
set_exception_handler('Core\Error::exceptionHandle');//Mesma logica do comando acima, so que neste sempre que o sistema encontra uma excessão ele executa o comando exceptionHandle existente dentro da nossa classe Error existente dentro da pasta Core.

/**
 * Routing
 */
$router = new Core\Router();

// Add the routes HERE:
$router->add('example', ['controller' => 'home', 'action' => 'index']);
//End custom routes

$router->add('', ['controller' => 'home', 'action' => 'index']);
$router->add('{controller}');
$router->add('{controller}/');
$router->add('{controller}/{action}');
$router->add('{controller}/{action}/{id}');


$router->dispatch($_SERVER['QUERY_STRING']);

