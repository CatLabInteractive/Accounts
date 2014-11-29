<?php

// Initialize router
$router = new \Neuron\Router ();

$signinmodule = new \CatLab\Accounts\ModuleController ();

$password = new \CatLab\Accounts\Authenticators\Password ();
$signinmodule->addAuthenticator ($password);

$router->module ('/account', $signinmodule);

return $router;