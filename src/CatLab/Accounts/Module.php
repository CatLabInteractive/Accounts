<?php

namespace CatLab\Accounts;

use CatLab\Accounts\Authenticators\Base\Authenticator;
use CatLab\Accounts\Collections\AuthenticatorCollection;
use CatLab\Accounts\Enums\Errors;
use CatLab\Accounts\Helpers\LoginForm;
use CatLab\Accounts\Mappers\UserMapper;
use CatLab\Accounts\Models\User;
use Neuron\Application;
use Neuron\Core\Template;
use Neuron\Core\Tools;
use Neuron\Exceptions\DataNotSet;
use Neuron\Exceptions\ExpectedType;
use Neuron\MapperFactory;
use Neuron\Models\Observable;
use Neuron\Net\QueryTrackingParameters;
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
     * @var bool
     */
    private $requireEmailValidationOnRegistration = false;

    /**
     * Minimum user age to be able to register.
     * @var int
     */
    private $minimumAge = 13;

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

            try {
                $session = $request->getSession();
            } catch (DataNotSet $e) {
                return null;
            }

            $userid = $session->get('catlab-user-id');

            if ($userid) {
                /** @var User $user */
                $user = MapperFactory::getUserMapper()->getFromId($userid);
                if ($user && !$user->isAnonymized()) {
                    return $user;
                } else {
                    $this->logout($request);
                }
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
        // Actually, we need to reload the user model to make sure we have fresh model
        /** @var User $user */
        $user = MapperFactory::getUserMapper()->getFromId($user->getId());

        // New account. Needs verification?
        if (
            !$user->isEmailVerified() &&
            (
                $this->requiresEmailValidation() ||
                $this->requiresEmailValidationOnRegistration()
            )
        ) {
            $user->generateVerificationEmail($this, $user->getEmail());
        } else {
            $user->sendConfirmationEmail($this);
        }

        return $this->login($request, $user, true);
    }

    /**
     * Change a users password.
     * @param Request $request
     * @param User $user
     * @param $newPassword
     * @return bool|string
     */
    public function changePassword(Request $request, User $user, $newPassword)
    {
        if (!Tools::checkInput($newPassword, 'password')) {
            return Errors::PASSWORD_INVALID;
        }

        $user->changePassword($this, $newPassword);
        return true;
    }

    /**
     * @param Request $request
     * @param User $user
     * @param $newEmailAddress
     * @return bool|string
     * @throws \CatLab\Mailer\Exceptions\MailException
     */
    public function changeEmail(Request $request, User $user, $newEmailAddress)
    {
        // Check if we already have someone with this email address

        // No change?
        if ($user->getEmail() === $newEmailAddress) {
            return true;
        }

        /** @var UserMapper $mapper */
        $mapper = MapperFactory::getUserMapper();

        $existingUser = $mapper->getFromEmail($newEmailAddress);
        if ($existingUser) {
            return Errors::EMAIL_DUPLICATE;
        }

        \CatLab\Accounts\MapperFactory::getEmailMapper()->removeForEmailAddress($newEmailAddress);

        $user->changeEmail($this, $newEmailAddress);
        return true;
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
        $requiresEmailValidation = $this->requiresEmailValidation();
        if ($registration) {
            $requiresEmailValidation = $this->requiresEmailValidationOnRegistration();
        }

        // Check for email validation
        if ($requiresEmailValidation) {
            if (!$user->isEmailVerified()) {
                // Also set in session... why wouldn't this be in session? :D
                $request->getSession()->set('userJustRegistered', $registration);
                $request->getSession()->set('catlab-non-verified-user-id', $user->getId());
                return Response::redirect(URLBuilder::getURL($this->routepath . '/notverified'));
            }
        }

        $request->getSession()->set('catlab-user-id', $user->getId());
        $request->clearUser();

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
        $router->addFilter('verifiedEmailAddress', array($this, 'requireVerifiedEmailAddressFilter'));

        // Routes
        $router->match('GET|POST', $this->routepath . '/login/{authenticator}', '\CatLab\Accounts\Controllers\LoginController@authenticator')
            ->filter('session');

        $router->match('GET', $this->routepath . '/login', '\CatLab\Accounts\Controllers\LoginController@login')
            ->filter('session');

        $router->match('GET', $this->routepath . '/welcome', '\CatLab\Accounts\Controllers\LoginController@welcome')
            ->filter('session')
            ->filter('authenticated');

        $router->match('GET', $this->routepath . '/next', '\CatLab\Accounts\Controllers\LoginController@next')
            ->filter('session')
            ->filter('authenticated');

        $router->match('GET|POST', $this->routepath . '/notverified', '\CatLab\Accounts\Controllers\LoginController@requiresVerification')
            ->filter('session');

        $router->match('GET|POST', $this->routepath . '/notverified/poll', '\CatLab\Accounts\Controllers\LoginController@isVerifiedPoll')
            ->filter('session');

        $router->match('GET|POST', $this->routepath . '/change-email', '\CatLab\Accounts\Controllers\LoginController@changeEmailAddress')
            ->filter('session');

        $router->match('GET', $this->routepath . '/logout', '\CatLab\Accounts\Controllers\LoginController@logout')
            ->filter('session');

        $router->match('GET', $this->routepath . '/cancel', '\CatLab\Accounts\Controllers\LoginController@cancel')
            ->filter('session');

        $router->match('GET|POST', $this->routepath . '/register/{authenticator}', '\CatLab\Accounts\Controllers\RegistrationController@authenticator')
            ->filter('session');

        $router->match('GET|POST', $this->routepath . '/register', '\CatLab\Accounts\Controllers\RegistrationController@register')
            ->filter('session');

        $router->get($this->routepath . '/verify/{id}', '\CatLab\Accounts\Controllers\LoginController@verify')
            ->filter('session');

        $router->get($this->routepath . '/age-gate', '\CatLab\Accounts\Controllers\LoginController@ageGate')
            ->filter('session');
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
     * @return bool
     */
    public function requiresEmailValidationOnRegistration()
    {
        return $this->requireEmailValidationOnRegistration;
    }

    /**
     * @param boolean $requireEmailValidationOnLogin
     * @param null $requireEmailValidationOnRegistration
     * @return self
     */
    public function requireEmailValidation(
        $requireEmailValidationOnLogin = true,
        $requireEmailValidationOnRegistration = null
    ) {
        $this->requireEmailValidation = $requireEmailValidationOnLogin;
        $this->requireEmailValidationOnRegistration = $requireEmailValidationOnRegistration !== null
            ? $requireEmailValidationOnRegistration : $this->requireEmailValidation;

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
            $redirect = $this->injectTrackingParameters($redirect);
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

    /**
     *
     * @param \Neuron\Models\Router\Filter $filter
     * @return bool|Response
     */
    public function requireVerifiedEmailAddressFilter(\Neuron\Models\Router\Filter $filter)
    {
        $user = $filter->getRequest()->getUser();
        if ($user) {
            if ($filter->getRequest()->getUser()->isEmailVerified()) {
                return true;
            } else {
                global $signinmodule;

                $template = new Template ('CatLab/Accounts/notverified/notverified.phpt');
                $template->set('layout', $signinmodule->getLayout());

                $canResend = true;
                if (
                    $filter->getRequest()->input('retry') ||
                    count(\CatLab\Accounts\MapperFactory::getEmailMapper()->getFromUser($user)) === 0
                ) {
                    $user->generateVerificationEmail($signinmodule, $user->getEmail());
                    $canResend = false;
                }

                $template->set('canResend', $canResend);
                $template->set('name', $user->getDisplayName());
                $template->set('user', $user);
                $template->set('resend_url', URLBuilder::getURL($filter->getRequest()->getUrl(), array_merge($_GET, [ 'retry' => 1 ])));

                $pollAction = URLBuilder::getURL($signinmodule->getRoutePath() . '/notverified/poll', [
                    'continue' => URLBuilder::getURL($filter->getRequest()->getUrl(), array_merge($_GET))
                ]);
                $template->set('pollAction', $pollAction);

                return Response::template($template);
            }
        }

        return Response::error('You must be authenticated', Response::STATUS_UNAUTHORIZED);
    }

    /**
     * @param \DateTime $birthdate
     * @return Response|true
     */
    public function checkAgeGate(\DateTime $birthdate)
    {
        $yearsOld = (new \DateTime())->diff(($birthdate))->y;
        if ($yearsOld < $this->getMinimumAge()) {
            return Response::redirect(URLBuilder::getURL($this->routepath . '/age-gate'));
        }

        return true;
    }

    /**
     * @return int
     */
    public function getMinimumAge()
    {
        return $this->minimumAge;
    }

    /**
     * @param string $redirectUrl
     * @return string
     */
    protected function injectTrackingParameters($redirectUrl)
    {
        $parameters = QueryTrackingParameters::instance()->queryParameters;

        $values = [];
        foreach ($parameters as $parameter) {
            if (isset($_GET[$parameter])) {
                $values[$parameter] = $_GET[$parameter];
            }
        }

        if (count($values) === 0) {
            return $redirectUrl;
        }

        if (strpos($redirectUrl, '?') === false) {
            $redirectUrl .= '?';
        } else {
            $redirectUrl .= '&';
        }

        $redirectUrl .= http_build_query($values);

        return $redirectUrl;
    }
}
