<?php
/**
 * Created by PhpStorm.
 * User: daedeloth
 * Date: 16/11/14
 * Time: 14:34
 */

namespace CatLab\Signin;

use CatLab\Signin\Interfaces\Authenticator;

class ModuleController
    implements \Neuron\Interfaces\Module
{

    public function __construct ()
    {
        \Neuron\Core\Template::addTemplatePath (dirname (__FILE__) . '/templates/', 'CatLab/Signin/');
    }


    /**
     * Register the routes required for this module.
     * @param \Neuron\Router $router
     * @param $prefix
     * @return mixed
     */
    public function setRoutes(\Neuron\Router $router, $prefix)
    {
        $router->match ('GET|POST', $prefix . '/login', '\CatLab\Signin\Controllers\LoginController@login');
        $router->match ('GET|POST', $prefix . '/register', '\CatLab\Signin\Controllers\RegisterController@register');
    }

    /**
     * Set template paths, config vars, etc
     * @return void
     */
    public function initialize()
    {
        // TODO: Implement initialize() method.
    }

    /**
     * Add an authenticator
     * @param Authenticator $authenticator
     */
    public function addAuthenticator (Authenticator $authenticator)
    {

    }
}