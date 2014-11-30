<?php
/**
 * Created by PhpStorm.
 * User: daedeloth
 * Date: 16/11/14
 * Time: 14:34
 */

namespace CatLab\Accounts;

use CatLab\Accounts\Collections\AuthenticatorCollection;
use CatLab\Accounts\Helpers\LoginForm;
use CatLab\Accounts\Authenticators\Authenticator;
use Neuron\Core\Template;
use Neuron\Router;
use Neuron\Tools\Text;

class Module
    implements \Neuron\Interfaces\Module
{
    /** @var AuthenticatorCollection $authenticators */
    private $authenticators;

    /** @var string $layout */
    private $layout = 'index.phpt';

    /** @var string $routepath */
    private $routepath;

    /**
     *
     */
    public function __construct ()
    {
        $this->authenticators = new AuthenticatorCollection ();
    }

    /**
     * Set template paths, config vars, etc
     * @param string $routepath The prefix that should be added to all route paths.
     * @return void
     */
    public function initialize ($routepath)
    {
        // Set path
        $this->routepath = $routepath;

        // Add templates
        Template::addPath (__DIR__ . '/templates/', 'CatLab/Accounts/');

        // Add locales
        Text::getInstance ()->addPath ('catlab.accounts', __DIR__ . '/locales/');

        // Add helper methods
        $helper = new LoginForm ($this);

        Template::addHelper ('CatLab.Accounts.LoginForm', $helper);
    }

    /**
     * @return string
     */
    public function getRoutePath ()
    {
        return $this->routepath;
    }

    /**
     * Register the routes required for this module.
     * @param Router $router
     * @return mixed
     */
    public function setRoutes (Router $router)
    {
        $router->match ('GET|POST', $this->routepath . '/login/{authenticator?}', '\CatLab\Accounts\Controllers\LoginController@login');
        $router->match ('GET', $this->routepath . '/logout', '\CatLab\Accounts\Controllers\LoginController@logout');

        $router->match ('GET|POST', $this->routepath . '/register', '\CatLab\Accounts\Controllers\RegistrationController@register');
    }

    /**
     * Add an authenticator
     * @param Authenticator $authenticator
     */
    public function addAuthenticator (Authenticator $authenticator)
    {
        $authenticator->setModule ($this);
        $this->authenticators[] = $authenticator;
    }

    /**
     * @return Authenticator[]
     */
    public function getAuthenticators ()
    {
        return $this->authenticators;
    }

    /**
     * Set a layout that will be used for all pages
     * @param string $layout
     */
    public function setLayout ($layout)
    {
        $this->layout = $layout;
    }

    /**
     * @return string
     */
    public function getLayout ()
    {
        return $this->layout;
    }
}