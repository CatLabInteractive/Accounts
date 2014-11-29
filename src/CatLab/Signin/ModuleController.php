<?php
/**
 * Created by PhpStorm.
 * User: daedeloth
 * Date: 16/11/14
 * Time: 14:34
 */

namespace CatLab\Signin;

use CatLab\Signin\Interfaces\Authenticator;
use Neuron\Core\Template;
use Neuron\Interfaces\Module;
use Neuron\Router;

class ModuleController
    implements Module
{
    /**
     * Set template paths, config vars, etc
     * @return void
     */
    public function initialize ()
    {
        Template::addTemplatePath (dirname (__FILE__) . '/templates/', 'CatLab/Signin/');
    }

    /**
     * Register the routes required for this module.
     * @param Router $router
     * @param $prefix
     * @return mixed
     */
    public function setRoutes (Router $router, $prefix)
    {
        $router->match ('GET|POST', $prefix . '/login', '\CatLab\Signin\Controllers\LoginController@login');
        $router->match ('GET|POST', $prefix . '/register', '\CatLab\Signin\Controllers\RegisterController@register');
    }

    /**
     * Add an authenticator
     * @param Authenticator $authenticator
     */
    public function addAuthenticator (Authenticator $authenticator)
    {

    }
}