<?php

error_reporting (E_ALL);

require_once '../vendor/autoload.php';

// Initialize router
$router = new \Neuron\Router ();

// Add the signin module
$signin = new \CatLab\Accounts\ModuleController ();
$signin->addAuthenticator (new \CatLab\Accounts\Authenticators\Password ());

$router->module ('/login', $signin);

$router->run ();