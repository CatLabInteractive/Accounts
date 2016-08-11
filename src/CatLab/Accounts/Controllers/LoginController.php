<?php

namespace CatLab\Accounts\Controllers;

use CatLab\Accounts\Models\User;
use CatLab\Accounts\MapperFactory;
use Neuron\Core\Template;
use Neuron\Exceptions\InvalidParameter;
use Neuron\Net\Response;
use Neuron\URLBuilder;

class LoginController
	extends Base
{
    /**
     * @return Response
     */
    public function welcome()
    {
        $template = new Template ('CatLab/Accounts/welcome.phpt');

        $user = $this->request->getUser();
        $template->set('name', $user->getUsername());
        $template->set('layout', $this->module->getLayout());

        if ($redirect = $this->request->getSession ()->get ('post-login-redirect'))
        {
            $this->request->getSession ()->set ('post-login-redirect', null);
            $this->request->getSession ()->set ('cancel-login-redirect', null);

            //return Response::redirect ($redirect);
        } else {
            $redirect = '/';
        }

        $template->set('redirect_url', $redirect);

        return Response::template ($template);
    }

    /**
     * @return Response
     */
	public function login ()
	{
		// Check for return tag
		if ($return = $this->request->input ('return')) {
			$this->request->getSession ()->set ('post-login-redirect', $return);
		}

		// Check for cancel tag
		if ($return = $this->request->input ('cancel')) {
			$this->request->getSession ()->set ('cancel-login-redirect', $return);
		}

		// Check if already registered
		if ($user = $this->request->getUser ('accounts')) {
            return $this->module->postLogin($this->request, $user);
        }

        // Check if this is our first visit
        $cookies = $this->request->getCookies();
        if (!isset($cookies['fv'])) {
            $response = Response::redirect($this->module->getRoutePath () . '/register');
            $response->setCookies(array( 'fv' => time() ));
            return $response;
        }

		$template = new Template ('CatLab/Accounts/login.phpt');

		$template->set ('layout', $this->module->getLayout ());
		$template->set ('action', URLBuilder::getURL ($this->module->getRoutePath () . '/login'));
		$template->set ('email', $this->request->input ('email'));

		if ($this->request->getSession ()->get ('cancel-login-redirect')) {
			$template->set ('cancel', URLBuilder::getURL ($this->module->getRoutePath () . '/cancel'));
		}

		$authenticators = $this->module->getAuthenticators ();
		foreach ($authenticators as $v) {
			$v->setRequest ($this->request);
		}

		$template->set ('authenticators', $authenticators);

		return Response::template ($template);
	}

	/**
	 * @param $id
	 * @return Response
	 * @throws InvalidParameter
	 */
	public function verify ($id) {

		$email = MapperFactory::getEmailMapper ()->getFromId ($id);

		if (!$email)
			return Response::error ('Invalid email verification', Response::STATUS_NOTFOUND);

		$token = $this->request->input ('token');
		if ($email->getToken () !== $token)
			return Response::error ('Invalid email verification', Response::STATUS_UNAUTHORIZED);

		if ($email->getUser ()->getEmail () !== $email->getEmail ())
			return Response::error ('Invalid email verification: email mismatch', Response::STATUS_INVALID_INPUT);

		if ($email->isExpired ())
			return Response::error ('Invalid email verification: token expired', Response::STATUS_INVALID_INPUT);

		$user = $email->getUser ();
		if (! ($user instanceof User)) {
			throw new InvalidParameter ("User type mismatch.");
		}

		$user->setEmailVerified (true);
		$mapper = \Neuron\MapperFactory::getUserMapper ();
		if (! ($mapper instanceof \CatLab\Accounts\Mappers\UserMapper)) {
			throw new InvalidParameter ("Mapper must be UserMapper instance.");
		}

		$mapper->update ($user);

		return $this->module->login ($this->request, $user);
	}

	/**
	 * @return Response
	 */
	public function requiresVerification ()
	{
		$user = null;

		$userId = $this->request->getSession ()->get ('catlab-non-verified-user-id');
		if ($userId) {
			$user = \Neuron\MapperFactory::getUserMapper ()->getFromId ($userId);
		}

		if (!$user || !($user instanceof User)) {
			return Response::error ('You are not logged in.');
		}

		if ($user->isEmailVerified ()) {
			return $this->module->login ($this->request, $user);
		}

		$template = new Template ('CatLab/Accounts/notverified.phpt');

		// Send verification.
		if ($this->request->input ('retry')) {
			$user->sendVerificationEmail ($this->module);
		}

		$template->set ('layout', $this->module->getLayout ());
		$template->set ('user', $user);
		$template->set ('resend_url', URLBuilder::getURL ($this->module->getRoutePath() . '/notverified', array ('retry' => 1)));

		return Response::template ($template);
	}

    /**
     * @param $token
     * @return Response
     */
	public function authenticator ($token)
	{
		$authenticator = $this->module->getAuthenticators ()->getFromToken ($token);

        if (!$authenticator)
        {
            return Response::error ('Authenticator not found', Response::STATUS_NOTFOUND);
        }

        $authenticator->setRequest ($this->request);

        return $authenticator->login ();
	}

	public function cancel ()
	{
		$cancel = $this->request->getSession ()->get ('cancel-login-redirect');

		if ($cancel)
		{
			$this->request->getSession ()->set ('post-login-redirect', null);
			$this->request->getSession ()->set ('cancel-login-redirect', null);

			return Response::redirect ($cancel);
		}
		else {
			return Response::redirect (URLBuilder::getURL ('/'));
		}
	}

	public function logout ()
	{
		// Check for return tag
		if ($return = $this->request->input ('return')) {
			$this->request->getSession ()->set ('post-login-redirect', $return);
		}

		// Check for cancel tag
		if ($return = $this->request->input ('cancel')) {
			$this->request->getSession ()->set ('cancel-login-redirect', $return);
		}

		return $this->module->logout ($this->request);
	}
}