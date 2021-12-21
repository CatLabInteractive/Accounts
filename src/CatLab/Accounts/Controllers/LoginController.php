<?php

namespace CatLab\Accounts\Controllers;

use CatLab\Accounts\Enums\Errors;
use CatLab\Accounts\Models\User;
use CatLab\Accounts\MapperFactory;
use CatLab\Base\Helpers\StringHelper;
use CatLab\SameSiteCookieSniffer\Sniffer;
use Neuron\Core\Template;
use Neuron\Exceptions\InvalidParameter;
use Neuron\Net\Response;
use Neuron\Tools\Text;
use Neuron\Tools\TokenGenerator;
use Neuron\URLBuilder;

class LoginController extends Base
{
    /**
     * @return Response
     * @throws \Neuron\Exceptions\DataNotSet
     */
    public function welcome()
    {
        $template = new Template ('CatLab/Accounts/welcome.phpt');

        $user = $this->request->getUser();
        $template->set('name', $user->getDisplayName());
        $template->set('layout', $this->module->getLayout());

        $redirect = URLBuilder::getURL($this->module->getRoutePath() . '/next');

        // Tracker
        $trackerEvents = array();

        // is user registered?
        $justRegistered = $this->request->input('registered') ||
            $this->request->getSession()->get('userJustRegistered');

        if ($justRegistered) {
            $trackerEvents[] = array(
                'event' => 'registration'
            );
        }

        $trackerEvents[] = array(
            'event' => 'login'
        );

        $template->set('redirect_url', $redirect);
        $template->set('tracker', $trackerEvents[0]);
        $template->set('trackers', $trackerEvents);
        $template->set('registered', $justRegistered);

        // Unset the session variable to mark this user as 'tracked'.
        if ($justRegistered) {
            $this->request->getSession()->set('userJustRegistered', false);
        }

        return Response::template($template);
    }

    /**
     * Redirect back to the app after going through the welcome page.
     * @return Response
     * @throws \Neuron\Exceptions\DataNotSet
     */
    public function next()
    {
        $redirect = $this->module->getAndClearPostLoginRedirect($this->request);
        return Response::redirect($redirect);
    }

    /**
     * @return Response
     * @throws \Neuron\Exceptions\DataNotSet
     * @throws InvalidParameter
     */
    public function login()
    {
        $this->module->setPostLoginRedirects($this->request);

        if ($this->request->input('skipWelcome')) {
            $this->request->getSession()->set('skip-welcome-redirect', $this->request->input('skipWelcome') ? 1 : 0);
        }

        // Check if already registered
        if ($user = $this->request->getUser('accounts')) {
            return $this->module->postLogin($this->request, $user);
        }

        // Check if this is our first visit
        $intend = $this->request->input('intend');
        $cookies = $this->request->getCookies();
        if (
            (!$intend && !isset($cookies['fv'])) ||
            $this->request->input('intend') === 'register'
        ) {
            setcookie('fv', time(), Sniffer::instance()->getCookieParameters([
                'expires' => time() + 60 * 60 * 24 * 365 * 2
            ]));

            $registrationController = new RegistrationController($this->module);
            $registrationController->setRequest($this->request);

            return $registrationController->register();
        }

        $template = new Template ('CatLab/Accounts/login.phpt');

        $template->set('layout', $this->module->getLayout());
        $template->set('action', URLBuilder::getURL($this->module->getRoutePath() . '/login'));
        $template->set('email', $this->request->input('email'));

        if ($this->request->getSession()->get('cancel-login-redirect')) {
            $template->set('cancel', URLBuilder::getURL($this->module->getRoutePath() . '/cancel'));
        }

        $authenticators = $this->module->getAuthenticators();
        foreach ($authenticators as $v) {
            $v->setRequest($this->request);
        }

        $template->set('authenticators', $authenticators);

        return Response::template($template);
    }

    /**
     * @param $id
     * @return Response
     * @throws InvalidParameter
     * @throws \Neuron\Exceptions\DataNotSet
     */
    public function verify($id)
    {
        $email = MapperFactory::getEmailMapper()->getFromId($id);

        if (!$email) {
            return Response::error('Invalid email verification', Response::STATUS_NOTFOUND);
        }

        $token = $this->request->input('token');
        if ($email->getToken() !== $token) {
            return Response::error('Invalid email verification', Response::STATUS_UNAUTHORIZED);
        }

        /*
        if ($email->getUser()->getEmail() !== $email->getEmail())
            return Response::error('Invalid email verification: email mismatch', Response::STATUS_INVALID_INPUT);
        */

        if ($email->isExpired()) {
            return Response::error('Invalid email verification: token expired', Response::STATUS_INVALID_INPUT);
        }

        $user = $email->getUser();
        if (!($user instanceof User)) {
            throw new InvalidParameter ("User type mismatch.");
        }

        $isEmailAddressChanged = $user->getEmail() !== $email->getEmail();

        $user->setEmail($email->getEmail());
        $user->setEmailVerified(true);
        $email->setVerified(true);

        MapperFactory::getEmailMapper()->update($email);

        // Call trigger of address was changed
        if ($isEmailAddressChanged) {
            $user->onEmailAddressChanged();
        }

        $mapper = \Neuron\MapperFactory::getUserMapper();
        if (!($mapper instanceof \CatLab\Accounts\Mappers\UserMapper)) {
            throw new InvalidParameter ("Mapper must be UserMapper instance.");
        }

        $mapper->update($user);

        // Check if the poll action is still active
        $lastPollAction = $this->request->getSession()->get('catlab-last-verify-poll');
        if ($lastPollAction && $lastPollAction < (time() - 10)) {
            // last poll action is way too long ago... redirect to the verified page
            $this->request->getSession()->set('catlab-last-verify-poll', 0);
            return $this->module->login($this->request, $user);
        }

        //return $this->module->login($this->request, $user);
        $template = new Template('CatLab/Accounts/notverified/verified.phpt');
        $template->set('layout', $this->module->getLayout());
        return Response::template($template);
    }

    /**
     * @return Response
     * @throws \Neuron\Exceptions\DataNotSet
     */
    public function requiresVerification()
    {
        $user = null;

        $userId = $this->request->getSession()->get('catlab-non-verified-user-id');
        if ($userId) {
            $user = \Neuron\MapperFactory::getUserMapper()->getFromId($userId);
        }

        if (!$user || !($user instanceof User)) {
            return Response::error('You are not logged in.');
        }

        if ($user->isEmailVerified()) {
            $didJustRegister = !!$this->request->getSession()->get('userJustRegistered');
            return $this->module->login($this->request, $user, $didJustRegister);
        }

        $canResend = true;
        $template = new Template ('CatLab/Accounts/notverified/notverified.phpt');

        // Send verification.
        if (
            $this->request->input('retry') ||
            count(MapperFactory::getEmailMapper()->getFromUser($user)) === 0
        ) {
            $user->generateVerificationEmail($this->module, $user->getEmail());
            $canResend = false;
        }

        $this->request->getSession()->set('catlab-last-verify-poll', time());

        $template->set('canResend', $canResend);
        $template->set('name', $user->getDisplayName());
        $template->set('layout', $this->module->getLayout());
        $template->set('user', $user);
        $template->set('resend_url', URLBuilder::getURL($this->module->getRoutePath() . '/notverified', array('retry' => 1)));
        $template->set('pollAction', URLBuilder::getURL($this->module->getRoutePath() . '/notverified/poll'));
        $template->set('changeAddress_url', URLBuilder::getURL(
            $this->module->getRoutePath() . '/change-email', array(
                'return' => URLBuilder::getURL($this->module->getRoutePath() . '/notverified'))
            )
        );

        return Response::template($template);
    }

    /**
     * @return Response
     * @throws \CatLab\Mailer\Exceptions\MailException
     * @throws \Neuron\Exceptions\DataNotSet
     */
    public function changeEmailAddress()
    {
        $text = Text::getInstance();
        $text->setDomain('messages');
        
        $user = $this->request->getUser();
        if (!$user) {
            $userId = $this->request->getSession()->get('catlab-non-verified-user-id');
            if ($userId) {
                $user = \Neuron\MapperFactory::getUserMapper()->getFromId($userId);
            }
        }

        if (!$user || !($user instanceof User)) {
            return Response::error('You are not logged in.');
        }

        $return = $this->request->input('return');
        $action = $this->request->input('action');

        $error = null;
        switch ($action) {
            case 'change-password':
                // check csfr
                if (!$this->isValidCsfrToken()) {
                    $error = 'Invalid request, please try again.';
                    break;
                }

                $email = $this->request->input('email', 'email');
                if ($email) {
                    $error = $this->module->changeEmail($this->request, $user, $email);
                    if ($error === true) {
                        if ($return) {
                            return Response::redirect($return);
                        } else {
                            return Response::redirect('/');
                        }
                    }
                }
                break;
        }

        $template = new Template ('CatLab/Accounts/notverified/changeAddress.phpt');

        $errorText = null;
        if ($error) {
            switch ($error) {
                case Errors::EMAIL_DUPLICATE:
                    $errorText = $text->getText('This email address is already in use for a different account.');
                    break;

                default:
                    $errorText = $text->getText('An undefined error has occurred.');
                    break;
            }
        }
        
        $template->set('error', $errorText);
        $template->set('name', $user->getDisplayName());
        $template->set('layout', $this->module->getLayout());
        $template->set('user', $user);
        $template->set('action', URLBuilder::getURL($this->module->getRoutePath() . '/change-email', [ 'return' => $return ]));
        $template->set('csfr', $this->generateCsfrToken());

        if (isset($return)) {
            $template->set('return_url', $return);
        }

        return Response::template($template);
    }

    /**
     *
     */
    public function isVerifiedPoll()
    {
        $user = $this->request->getUser();
        if (!$user) {
            $userId = $this->request->getSession()->get('catlab-non-verified-user-id');
            if ($userId) {
                $user = \Neuron\MapperFactory::getUserMapper()->getFromId($userId);
            }
        }

        if (!$user || !($user instanceof User)) {
            return Response::json([
                'error' => [
                    'message' => 'You are not logged in.'
                ]
            ])->setStatus(403);
        }

        $this->request->getSession()->set('catlab-last-verify-poll', time());

        if ($user->isEmailVerified()) {

            $continue = $this->request->input('continue');
            if (!$continue) {
                $continue = URLBuilder::getURL($this->module->getRoutePath() . '/notverified');
            }

            return Response::json([
                'verified' => true,
                'redirect' => $continue
            ]);
        } else {
            return Response::json([
                'verified' => false,
                'wait' => 5000
            ]);
        }
    }

    /**
     * @param $token
     * @return Response
     * @throws \Neuron\Exceptions\DataNotSet
     */
    public function authenticator($token)
    {
        $this->module->setPostLoginRedirects($this->request);

        $authenticator = $this->module->getAuthenticators()->getFromToken($token);

        if (!$authenticator) {
            return Response::error('Authenticator not found', Response::STATUS_NOTFOUND);
        }

        $authenticator->setRequest($this->request);

        return $authenticator->login();
    }

    /**
     * @return Response
     * @throws \Neuron\Exceptions\DataNotSet
     */
    public function cancel()
    {
        $cancel = $this->module->getAndClearCancelLoginRedirect($this->request);

        if ($cancel) {
            return Response::redirect($cancel);
        } else {
            return Response::redirect(URLBuilder::getURL('/'));
        }
    }

    /**
     * @return Response
     * @throws \Neuron\Exceptions\DataNotSet
     */
    public function logout()
    {
        $this->module->setPostLoginRedirects($this->request);
        return $this->module->logout($this->request);
    }

    /**
     * @return Response
     */
    public function ageGate()
    {
        return Response::template('CatLab/Accounts/agegate.phpt', [
            'layout' => $this->module->getLayout(),
            'return' => URLBuilder::getURL($this->module->getRoutePath() . '/register/password'),
            'module' => $this->module
        ]);
    }

    /**
     * @return bool
     * @throws \Neuron\Exceptions\DataNotSet
     */
    protected function isValidCsfrToken()
    {
        if (!$this->request->getSession()->get('csfr-token')) {
            return false;
        }

        if ($this->request->input('csfr-token') !== $this->request->getSession()->get('csfr-token')) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     * @throws \Neuron\Exceptions\DataNotSet
     */
    protected function generateCsfrToken()
    {
        $csfr = TokenGenerator::getToken(32);
        $this->request->getSession()->set('csfr-token', $csfr);

        return $csfr;
    }
}
