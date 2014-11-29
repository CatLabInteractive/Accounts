<?php
/**
 * Created by PhpStorm.
 * User: daedeloth
 * Date: 16/11/14
 * Time: 14:34
 */

namespace CatLab\Accounts;

use CatLab\Accounts\Helpers\LoginForm;
use CatLab\Accounts\Interfaces\Authenticator;
use Neuron\Core\Template;
use Neuron\Interfaces\Module;
use Neuron\Router;

class ModuleController
    implements Module
{
    /** @var Authenticator[] $authenticators */
    private $authenticators = array ();

    /**
     * Set template paths, config vars, etc
     * @return void
     */
    public function initialize ()
    {
        Template::addPath (__DIR__ . '/templates/', 'CatLab/Accounts/');

        // Add locales
        \Neuron\Tools\Text::getInstance ()->addPath ('catlab.accounts', __DIR__ . '/locales/');

        // Add helper methods
        $helper = new LoginForm ($this);

        Template::addHelper ('CatLab.Accounts.LoginForm', $helper);
    }

    /**
     * Register the routes required for this module.
     * @param Router $router
     * @param $prefix
     * @return mixed
     */
    public function setRoutes (Router $router, $prefix)
    {
        $router->match ('GET|POST', $prefix . '/login', '\CatLab\Accounts\Controllers\LoginController@login');
        $router->match ('GET', $prefix . '/logout', '\CatLab\Accounts\Controllers\LoginController@logout');

        $router->match ('GET|POST', $prefix . '/register', '\CatLab\Accounts\Controllers\RegistrationController@register');
    }

    /**
     * Add an authenticator
     * @param Authenticator $authenticator
     */
    public function addAuthenticator (Authenticator $authenticator)
    {
        $this->authenticators[] = $authenticator;
    }
}