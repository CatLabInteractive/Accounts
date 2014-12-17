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
use CatLab\Accounts\Models\User;
use Neuron\Application;
use Neuron\Core\Template;
use Neuron\Net\Request;
use Neuron\Net\Response;
use Neuron\Router;
use Neuron\Tools\Text;
use Neuron\URLBuilder;

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

        // Set session variable
        Application::getInstance ()->on ('dispatch:before', array ($this, 'setRequestUser'));

        // Add helper methods
        $helper = new LoginForm ($this);

        Template::addHelper ('CatLab.Accounts.LoginForm', $helper);
    }

    /**
     * Set user from session
     * @param Request $request
     */
    public function setRequestUser (Request $request)
    {
        $request->setUserCallback (function (Request $request) {

            $userid = $request->getSession ()->get ('catlab-user-id');

            if ($userid)
            {
                $user = MapperFactory::getUserMapper ()->getFromId ($userid);
                if ($user)
                    return $user;
            }

            return null;
        });
    }

    /**
     * Login a specific user
     * @param Request $request
     * @param User $user
     * @return \Neuron\Net\Response
     */
    public function login (Request $request, User $user)
    {
        $request->getSession ()->set ('catlab-user-id', $user->getId ());
        return $this->postLogin ($request, $user);
    }

    /**
     * Logout user
     * @param Request $request
     * @throws \Neuron\Exceptions\DataNotSet
     * @return \Neuron\Net\Response
     */
    public function logout (Request $request)
    {
        $request->getSession ()->set ('catlab-user-id', null);
        return $this->postLogout ($request);
    }

    /**
     * Called right after a user is logged in.
     * Should be a redirect.
     * @param Request $request
     * @param \Neuron\Interfaces\Models\User $user
     * @return \Neuron\Net\Response
     */
    public function postLogin (Request $request, \Neuron\Interfaces\Models\User $user)
    {
        if ($redirect = $request->getSession ()->get ('post-login-redirect'))
        {
            $request->getSession ()->set ('post-login-redirect', null);
            $request->getSession ()->set ('cancel-login-redirect', null);

            return Response::redirect ($redirect);
        }

        //return Response::redirect (URLBuilder::getURL ('/'));
    }

    /**
     * Called after a redirect
     * @param Request $request
     * @return Response
     */
    public function postLogout (Request $request)
    {
        return Response::redirect (URLBuilder::getURL ('/'));
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
        $router->match ('GET|POST', $this->routepath . '/login/{authenticator}', '\CatLab\Accounts\Controllers\LoginController@authenticator');
        $router->match ('GET', $this->routepath . '/login', '\CatLab\Accounts\Controllers\LoginController@login');

        $router->match ('GET', $this->routepath . '/logout', '\CatLab\Accounts\Controllers\LoginController@logout');

        $router->match ('GET|POST', $this->routepath . '/register/{authenticator}', '\CatLab\Accounts\Controllers\RegistrationController@authenticator');
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
     * @return AuthenticatorCollection
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