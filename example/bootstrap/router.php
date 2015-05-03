<?php

// Initialize router
$router = new \Neuron\Router ();

// Accounts module
$signinmodule = new \CatLab\Accounts\Module ();
$signinmodule->setLayout ('index-account.phpt');

$password = new \CatLab\Accounts\Authenticators\Password ();
$signinmodule->addAuthenticator ($password);

$facebook = new \CatLab\Accounts\Authenticators\Facebook ();
$signinmodule->addAuthenticator ($facebook);

$steam = new \CatLab\Accounts\Authenticators\Steam ();
$signinmodule->addAuthenticator ($steam);

// Make the module available on /account
$router->module ('/account', $signinmodule);

$router->get ('/thirdparty', function () {

	$request = \Neuron\Application::getInstance ()->getRouter ()->getRequest ();

	$deligatedAccounts = $request->getUser ()->getDeligatedAccounts ();
	return \Neuron\Net\Response::template ('thirdparty.phpt', array ('accounts' => $deligatedAccounts));

})->filter ('authenticated');

// Catch the default route
$router->get ('/', function () {
	return \Neuron\Net\Response::template ('home.phpt');
});

return $router;