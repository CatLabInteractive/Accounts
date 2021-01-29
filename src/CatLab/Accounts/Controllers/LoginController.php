<?php

namespace CatLab\Accounts\Controllers;

use CatLab\Accounts\Models\User;
use CatLab\Accounts\MapperFactory;
use CatLab\SameSiteCookieSniffer\Sniffer;
use Neuron\Core\Template;
use Neuron\Exceptions\InvalidParameter;
use Neuron\Net\Response;
use Neuron\URLBuilder;

class LoginController
    extends Base
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
        if (
            $this->request->input('registered') ||
            $this->request->getSession()->get('userJustRegistered')
        ) {
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
        $cookies = $this->request->getCookies();
        if (!isset($cookies['fv'])) {
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

        if (!$email)
            return Response::error('Invalid email verification', Response::STATUS_NOTFOUND);

        $token = $this->request->input('token');
        if ($email->getToken() !== $token)
            return Response::error('Invalid email verification', Response::STATUS_UNAUTHORIZED);

        /*
        if ($email->getUser()->getEmail() !== $email->getEmail())
            return Response::error('Invalid email verification: email mismatch', Response::STATUS_INVALID_INPUT);
        */

        if ($email->isExpired())
            return Response::error('Invalid email verification: token expired', Response::STATUS_INVALID_INPUT);

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

        //return $this->module->login($this->request, $user);
        $template = new Template('CatLab/Accounts/verified.phpt');
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
            return $this->module->login($this->request, $user);
        }

        $canResend = true;
        $template = new Template ('CatLab/Accounts/notverified.phpt');

        // Send verification.
        if (
            $this->request->input('retry') ||
            count(MapperFactory::getEmailMapper()->getFromUser($user)) === 0
        ) {
            $user->generateVerificationEmail($this->module, $user->getEmail());
            $canResend = false;
        }

        $template->set('canResend', $canResend);
        $template->set('name', $user->getDisplayName());
        $template->set('layout', $this->module->getLayout());
        $template->set('user', $user);
        $template->set('resend_url', URLBuilder::getURL($this->module->getRoutePath() . '/notverified', array('retry' => 1)));
        $template->set('pollAction', URLBuilder::getURL($this->module->getRoutePath() . '/notverified/poll'));

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
}
