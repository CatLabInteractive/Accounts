<?php

error_reporting (E_ALL);

require_once '../vendor/autoload.php';

// Initialize router
$router = new \Neuron\Router ();

// Add the signin module
$signin = new \CatLab\Signin\ModuleController ();
$signin->addAuthenticator (new Password ());

$router->module ('/login', $signin);

$router->run ();