<?php

namespace CatLab\Accounts;

use CatLab\Accounts\Authenticators\Base\Authenticator;
use CatLab\Accounts\Collections\AuthenticatorCollection;
use CatLab\Accounts\Helpers\LoginForm;
use CatLab\Accounts\Mappers\UserMapper;
use CatLab\Accounts\Models\User;
use Neuron\Application;
use Neuron\Core\Template;
use Neuron\Exceptions\DataNotSet;
use Neuron\Exceptions\ExpectedType;
use Neuron\MapperFactory;
use Neuron\Models\Observable;
use Neuron\Net\Request;
use Neuron\Net\Response;
use Neuron\Router;
use Neuron\Tools\Text;
use Neuron\URLBuilder;

/**
 * Class Module
 * @package CatLab\Accounts
 */
class Module extends Observable
    implements \Neuron\Interfaces\Module
{
    /** @var AuthenticatorCollection $authenticators */
    private $authenticators;

    /** @var string $layout */
    private $layout = 'index.phpt';

    /** @var string $routepath */
    private $routepath;

    /**
     * @var bool
     */
    private $requireEmailValidation = false;

    /**
     *
     */
    public function __construct()
    {
        $this->authenticators = new AuthenticatorCollection ();
    }

    /**
     * Set template paths, config vars, etc
     * @param string $routepath The prefix that should be added to all route paths.
     * @return void
     */
    public function initialize($routepath)
    {
        // Set path
        $this->routepath = $routepath;

        // Add templates
        Template::addPath(__DIR__ . '/templates/', 'CatLab/Accounts/');

        // Add locales
        Text::getInstance()->addPath('catlab.accounts', __DIR__ . '/locales/');

        // Set session variable
        Application::getInstance()->on('dispatch:before', array($this, 'setRequestUser'));

        // Set the global user mapper, unless one is set already
        Application::getInstance()->on('dispatch:first', array($this, 'setUserMapper'));

        // Add helper methods
        $helper = new LoginForm ($this);

        Template::addHelper('CatLab.Accounts.LoginForm', $helper);
    }

    /**
     * Set user from session
     * @param Request $request
     * @throws \Neuron\Exceptions\InvalidParameter
     */
    public function setRequestUser(Request $request)
    {
        $request->addUserCallback('accounts', function (Request $request) {

            $userid = $request->getSession()->get('catlab-user-id');

            if ($userid) {
                $user = MapperFactory::getUserMapper()->getFromId($userid);
                if ($user)
                    return $user;
            }

            return null;
        });
    }

    public function setUserMapper()
    {
        try {
            $mapper = MapperFactory::getUserMapper();
            ExpectedType::check($mapper, UserMapper::class);
        } catch (DataNotSet $e) {
            MapperFactory::getInstance()->setMapper('user', new UserMapper ());
        }
    }

    /**
     * @param Request $request
     * @param User $user
     * @return Response
     * @throws DataNotSet
     */
    public function register(Request $request, User $user)
    {
        // New account. Needs verification?
        if ($this->requiresEmailValidation()) {
            $user->sendVerificationEmail($this);
        } else {
            $user->sendConfirmationEmail($this);
        }

        return $this->login($request, $user, true);
    }

    /**
     * Login a specific user
     * @param Request $request
     * @param User $user
     * @param bool $registration
     * @return \Neuron\Net\Response
     * @throws DataNotSet
     */
    public function login(Request $request, User $user, $registration = false)
    {
        // Check for email validation
        if ($this->requiresEmailValidation()) {
            if (!$user->isEmailVerified()) {
                $request->getSession()->set('catlab-non-verified-user-id', $user->getId());
                return Response::redirect(URLBuilder::getURL($this->routepath . '/notverified'));
            }
        }

        $request->getSession()->set('catlab-user-id', $user->getId());
        return $this->postLogin($request, $user, $registration);
    }

    /**
     * Logout user
     * @param Request $request
     * @return \Neuron\Net\Response
     * @throws \Neuron\Exceptions\DataNotSet
     */
    public function logout(Request $request)
    {
        $request->getSession()->set('catlab-user-id', null);
        $request->getSession()->set('catlab-non-verified-user-id', null);
        return $this->postLogout($request);
    }

    /**
     * Called right after a user is logged in.
     * Should be a redirect.
     * @param Request $request
     * @param \Neuron\Interfaces\Models\User $user
     * @param boolean $registered
     * @return \Neuron\Net\Response
     * @throws DataNotSet
     */
    public function postLogin(Request $request, \Neuron\Interfaces\Models\User $user, $registered = false)
    {
        $parameters = array();
        if ($registered) {
            $parameters['registered'] = 1;
        }

        // Also set in session... why wouldn't this be in session? :D
        $request->getSession()->set('userJustRegistered', $registered);

        $this->trigger('user:login', [
            'request' => $request,
            'user' => $user,
            'registered' => $registered
        ]);

        // Should skip welcome screen?
        if ($request->getSession()->get('skip-welcome-redirect')) {
            return $this->redirectBackToApp([]);
        }

        return $this->redirectToWelcome([]);
    }

    /**
     * Redirect user to welcome page.
     * @param $parameters
     * @return Response
     */
    public function redirectToWelcome($parameters)
    {
        return Response::redirect(
            URLBuilder::getURL(
                $this->getRoutePath() . '/welcome',
                $parameters
            )
        );
    }

    /**
     * Redirect back to the orginal app, but go through the 'next' endpoint.
     * @param $parameters
     * @return Response
     */
    public function redirectBackToApp($parameters)
    {
        return Response::redirect(
            URLBuilder::getURL(
                $this->getRoutePath() . '/next',
                $parameters
            )
        );
    }

    /**
     * Called after a redirect
     * @param Request $request
     * @return Response
     * @throws DataNotSet
     */
    public function postLogout(Request $request)
    {
        $redirect = $this->getAndClearPostLoginRedirect($request);

        if ($redirect) {
            return Response::redirect($redirect);
        }

        return Response::redirect(URLBuilder::getURL('/'));
    }

    /**
     * @return string
     */
    public function getRoutePath()
    {
        return $this->routepath;
    }

    /**
     * Register the routes required for this module.
     * @param Router $router
     * @return mixed
     */
    public function setRoutes(Router $router)
    {
        // Filter
        $router->addFilter('authenticated', array($this, 'routerVerifier'));

        // Routes
        $router->match('GET|POST', $this->routepath . '/login/{authenticator}', '\CatLab\Accounts\Controllers\LoginController@authenticator');
        $router->match('GET', $this->routepath . '/login', '\CatLab\Accounts\Controllers\LoginController@login');
        $router->match('GET', $this->routepath . '/welcome', '\CatLab\Accounts\Controllers\LoginController@welcome')->filter('authenticated');
        $router->match('GET', $this->routepath . '/next', '\CatLab\Accounts\Controllers\LoginController@next')->filter('authenticated');

        $router->match('GET|POST', $this->routepath . '/notverified', '\CatLab\Accounts\Controllers\LoginController@requiresVerification');

        $router->match('GET', $this->routepath . '/logout', '\CatLab\Accounts\Controllers\LoginController@logout');

        $router->match('GET', $this->routepath . '/cancel', '\CatLab\Accounts\Controllers\LoginController@cancel');

        $router->match('GET|POST', $this->routepath . '/register/{authenticator}', '\CatLab\Accounts\Controllers\RegistrationController@authenticator');
        $router->match('GET|POST', $this->routepath . '/register', '\CatLab\Accounts\Controllers\RegistrationController@register');

        $router->get($this->routepath . '/verify/{id}', '\CatLab\Accounts\Controllers\LoginController@verify');
    }

    /**
     * Add an authenticator
     * @param Authenticator $authenticator
     */
    public function addAuthenticator(Authenticator $authenticator)
    {
        $authenticator->setModule($this);
        $this->authenticators[] = $authenticator;
    }

    /**
     * @return AuthenticatorCollection
     */
    public function getAuthenticators()
    {
        return $this->authenticators;
    }

    /**
     * Set a layout that will be used for all pages
     * @param string $layout
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;
    }

    /**
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }

    public function routerVerifier(\Neuron\Models\Router\Filter $filter)
    {
        if ($filter->getRequest()->getUser()) {
            return true;
        }

        return Response::error('You must be authenticated', Response::STATUS_UNAUTHORIZED);
    }

    /**
     * @return boolean
     */
    public function requiresEmailValidation()
    {
        return $this->requireEmailValidation;
    }

    /**
     * @param boolean $requireEmailValidation
     * @return self
     */
    public function requireEmailValidation($requireEmailValidation = true)
    {
        $this->requireEmailValidation = $requireEmailValidation;
        return $this;
    }

    /**
     * @param Request $request
     * @return string
     * @throws DataNotSet
     */
    public function getAndClearPostLoginRedirect(Request $request)
    {
        $this->clearExpiredSessionAttributes($request);

        if ($redirect = $request->getSession()->get('post-login-redirect')) {
            return $redirect;
        } else {
            return '/';
        }
    }

    /**
     * @param Request $request
     * @return string
     * @throws DataNotSet
     */
    public function getAndClearCancelLoginRedirect(Request $request)
    {
        $this->clearExpiredSessionAttributes($request);

        if ($redirect = $request->getSession()->get('cancel-login-redirect')) {
            return $redirect;
        } else {
            return '/';
        }
    }

    /**
     * @param Request $request
     * @throws DataNotSet
     */
    private function clearExpiredSessionAttributes(Request $request)
    {
        if (
            $request->getSession()->get('post-login-redirect-expires') &&
            $request->getSession()->get('post-login-redirect-expires') < time()
        ) {
            $request->getSession()->set('post-login-redirect', null);
            $request->getSession()->set('cancel-login-redirect', null);
            $request->getSession()->set('post-login-redirect-expires', null);
        } else {
            // schedule expiration in 5, 4, 3, 2 ...
            $request->getSession()->set('post-login-redirect-expires', time() + 5);
        }
    }

    /**
     * @param Request $request
     * @throws DataNotSet
     */
    public function setPostLoginRedirects(Request $request)
    {
        // Check for return tag
        if ($return = $request->input('return')) {
            $request->getSession()->set('post-login-redirect', $return);
            $request->getSession()->set('post-login-redirect-expires', null);
        }

        // Check for cancel tag
        if ($return = $request->input('cancel')) {
            $request->getSession()->set('cancel-login-redirect', $return);
            $request->getSession()->set('post-login-redirect-expires', null);
        }
    }
}
